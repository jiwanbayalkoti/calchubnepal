<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoRedirect;
use App\Services\Seo\SeoAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SeoToolsController extends Controller
{
    public function audit(SeoAuditService $audit): View
    {
        return view('admin.seo.audit', [
            'report' => $audit->report(),
        ]);
    }

    public function redirects(): View
    {
        return view('admin.seo.redirects');
    }

    public function redirectsData(Request $request): JsonResponse
    {
        $query = SeoRedirect::query()->latest('id');

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('from_path', 'like', "%{$search}%")
                    ->orWhere('to_url', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $total = SeoRedirect::query()->count();
        $filtered = (clone $query)->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

        $rows = $query->skip($start)->take($length)->get()->map(fn (SeoRedirect $r) => [
            'id' => $r->id,
            'from_path' => $r->from_path,
            'to_url' => $r->to_url,
            'status_code' => $r->status_code,
            'is_active' => $r->is_active,
            'hit_count' => $r->hit_count,
            'note' => $r->note,
        ]);

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows,
        ]);
    }

    public function storeRedirect(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500'],
            'to_url' => ['required', 'string', 'max:1000'],
            'status_code' => ['required', 'integer', 'in:301,302,307,308'],
            'is_active' => ['sometimes', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $data['from_path'] = SeoRedirect::normalizePath($data['from_path']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = $request->user()?->id;
        $data['updated_by'] = $request->user()?->id;

        $redirect = SeoRedirect::query()->updateOrCreate(
            ['from_path' => $data['from_path']],
            $data
        );

        Cache::forget('seo_redirects_map_v1');

        return response()->json(['success' => true, 'message' => 'Redirect saved.', 'data' => $redirect]);
    }

    public function updateRedirect(Request $request, int $id): JsonResponse
    {
        $redirect = SeoRedirect::query()->findOrFail($id);

        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500'],
            'to_url' => ['required', 'string', 'max:1000'],
            'status_code' => ['required', 'integer', 'in:301,302,307,308'],
            'is_active' => ['sometimes', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $data['from_path'] = SeoRedirect::normalizePath($data['from_path']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['updated_by'] = $request->user()?->id;

        $redirect->update($data);
        Cache::forget('seo_redirects_map_v1');

        return response()->json(['success' => true, 'message' => 'Redirect updated.']);
    }

    public function destroyRedirect(int $id): JsonResponse
    {
        SeoRedirect::query()->whereKey($id)->delete();
        Cache::forget('seo_redirects_map_v1');

        return response()->json(['success' => true, 'message' => 'Redirect deleted.']);
    }
}
