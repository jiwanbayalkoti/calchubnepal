<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict access to the advertiser portal.
 */
class EnsureUserIsAdvertiser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->deny($request, 401, 'You must be logged in to access the advertiser portal.');
        }

        if (! $user->isAdvertiser()) {
            return $this->deny($request, 403, 'You do not have permission to access the advertiser portal.');
        }

        $advertiser = $user->advertiser;

        if (! $advertiser || $advertiser->status === 'suspended') {
            return $this->deny($request, 403, 'Your advertiser account is not available.');
        }

        if (! $user->is_active || $advertiser->status === 'inactive') {
            return $this->deny($request, 403, 'Your advertiser account is inactive.');
        }

        return $next($request);
    }

    private function deny(Request $request, int $status, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        abort($status, $message);
    }
}
