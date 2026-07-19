<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate a route behind a specific permission slug, e.g.
 * `Route::middleware('permission:calculators.create')`.
 *
 * Super-admins implicitly pass every permission check.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->deny($request, 401, 'You must be logged in to perform this action.');
        }

        $allowed = $user->hasRole('super-admin') || $user->hasPermission($permission);

        if (! $allowed) {
            return $this->deny($request, 403, 'You do not have permission to perform this action.');
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
