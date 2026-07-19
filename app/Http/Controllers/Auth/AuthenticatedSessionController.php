<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view (full page fallback / redirect to modal on home).
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->boolean('page')) {
            return redirect()->route('home', ['auth' => 'login']);
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();
        $user?->forceFill(['last_login_at' => now()])->save();

        if ($user && filled($user->locale)) {
            $supported = (array) config('calculator_hub.locales', ['en', 'ne']);
            if (in_array($user->locale, $supported, true)) {
                $request->session()->put('locale', $user->locale);
                app()->setLocale($user->locale);
            }
        }

        $default = $user?->canAccessAdmin()
            ? route('admin.dashboard', absolute: false)
            : route('account.dashboard', absolute: false);

        $redirectTo = $request->session()->pull('url.intended', $default);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Logged in successfully.',
                'redirect' => $redirectTo,
            ]);
        }

        return redirect()->to($redirectTo);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
