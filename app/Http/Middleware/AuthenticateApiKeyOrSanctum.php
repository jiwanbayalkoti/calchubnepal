<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accepts Sanctum bearer OR custom X-Api-Key / Authorization: Bearer <api_key>.
 */
class AuthenticateApiKeyOrSanctum
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        $plain = $request->header('X-Api-Key')
            ?: (str_starts_with((string) $request->header('Authorization'), 'Bearer ')
                ? trim(substr((string) $request->header('Authorization'), 7))
                : null);

        if (! filled($plain)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Prefer Sanctum personal access token if it looks like one
        if (str_contains($plain, '|')) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(static fn () => $user);

                return $next($request);
            }
        }

        $apiKey = ApiKey::findByPlainTextKey($plain);
        if (! $apiKey || $apiKey->isExpired() || ! $apiKey->user) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();
        Auth::setUser($apiKey->user);
        $request->setUserResolver(static fn () => $apiKey->user);

        return $next($request);
    }
}
