<?php

namespace App\Http\Middleware;

use App\Models\SeoRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies admin-managed 301/302 redirects before the router resolves a page.
 */
class ApplySeoRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $path = SeoRedirect::normalizePath('/'.$request->path());

        /** @var array<string, array{to: string, code: int}> $map */
        $map = Cache::remember('seo_redirects_map_v1', 300, function () {
            return SeoRedirect::query()
                ->where('is_active', true)
                ->get(['from_path', 'to_url', 'status_code'])
                ->mapWithKeys(fn (SeoRedirect $row) => [
                    SeoRedirect::normalizePath($row->from_path) => [
                        'to' => $row->to_url,
                        'code' => (int) $row->status_code,
                    ],
                ])
                ->all();
        });

        if (! isset($map[$path])) {
            return $next($request);
        }

        $target = $map[$path]['to'];
        $code = in_array($map[$path]['code'], [301, 302, 307, 308], true) ? $map[$path]['code'] : 301;

        SeoRedirect::query()
            ->where('from_path', $path)
            ->orWhere('from_path', ltrim($path, '/'))
            ->increment('hit_count');

        if (! str_starts_with($target, 'http://') && ! str_starts_with($target, 'https://')) {
            $target = url($target);
        }

        return redirect()->away($target, $code);
    }
}
