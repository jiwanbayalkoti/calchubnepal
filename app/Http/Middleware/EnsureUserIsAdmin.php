<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict access to the admin panel to authenticated users who hold the
 * `super-admin` or `admin` role, or who have been explicitly granted the
 * `admin.dashboard.view` permission through a custom role.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->deny($request, 401, 'You must be logged in to access the admin panel.');
        }

        $isAdmin = $user->hasRole('super-admin')
            || $user->hasRole('admin')
            || $user->hasPermission('admin.dashboard.view');

        if (! $isAdmin) {
            if ($user->isAdvertiser()) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Use the advertiser portal.', 'redirect' => route('advertiser.dashboard')], 403);
                }

                return redirect()->route('advertiser.dashboard');
            }

            return $this->deny($request, 403, 'You do not have permission to access the admin panel.');
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
