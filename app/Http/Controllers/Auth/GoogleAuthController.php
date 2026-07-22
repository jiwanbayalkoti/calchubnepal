<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Admin\UserRegistered;
use App\Services\Activity\ActivityLogService;
use App\Services\Admin\AdminNotifier;
use GuzzleHttp\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLog,
        protected AdminNotifier $notifier,
    ) {
    }

    public function redirect(): RedirectResponse
    {
        return $this->google()->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = $this->google()->user();
        } catch (InvalidStateException $e) {
            report($e);

            return $this->fail('Google sign-in session expired. Please try again.');
        } catch (Throwable $e) {
            report($e);

            $hint = str_contains($e->getMessage(), 'SSL certificate') || str_contains($e->getMessage(), 'cURL error 60')
                ? ' (SSL certificate issue on this server — contact the site admin.)'
                : '';

            return $this->fail('Google sign-in failed. Please try again.'.$hint);
        }

        $email = strtolower((string) $googleUser->getEmail());
        $googleId = (string) $googleUser->getId();

        if ($email === '' || $googleId === '') {
            return $this->fail('Google did not return a valid email address.');
        }

        $isNewUser = false;

        try {
            $user = DB::transaction(function () use ($googleUser, $email, $googleId, &$isNewUser) {
                $user = User::query()->where('google_id', $googleId)->first()
                    ?? User::query()->where('email', $email)->first();

                $userRoleId = Role::query()->where('slug', 'user')->value('id');

                if ($user) {
                    $user->fill([
                        'google_id' => $googleId,
                        'name' => $user->name ?: ($googleUser->getName() ?: Str::before($email, '@')),
                        'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'is_active' => $user->is_active ?? true,
                        'role_id' => $user->role_id ?: $userRoleId,
                        'last_login_at' => now(),
                    ])->save();

                    return $user->fresh();
                }

                $isNewUser = true;

                return User::query()->create([
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $googleUser->getAvatar(),
                    'password' => null,
                    'email_verified_at' => now(),
                    'role_id' => $userRoleId,
                    'is_active' => true,
                    'locale' => config('calculator_hub.default_locale', 'en'),
                    'last_login_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            report($e);

            return $this->fail('Could not create your account from Google. Please try again.');
        }

        if ($isNewUser) {
            $this->notifier->notify(new UserRegistered($user, 'google'));
        }

        if (! $user->is_active) {
            return $this->fail('Your account is inactive. Please contact support.');
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        $supported = (array) config('calculator_hub.locales', ['en', 'ne']);
        $locale = filled($user->locale) && in_array($user->locale, $supported, true)
            ? $user->locale
            : (session('locale') ?: app()->getLocale());
        if (in_array($locale, $supported, true)) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
            if (! filled($user->locale)) {
                $user->forceFill(['locale' => $locale])->save();
            }
        }

        $this->activityLog->log('login', 'auth', $user, [
            'provider' => 'google',
            'email' => $user->email,
        ]);

        $default = $user->canAccessAdmin()
            ? route('admin.dashboard', absolute: false)
            : route('account.dashboard', absolute: false);

        return redirect()->intended($default);
    }

    protected function google(): Provider
    {
        $driver = Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email']);

        $caBundle = $this->caBundlePath();
        if ($caBundle) {
            $driver->setHttpClient(new Client([
                'verify' => $caBundle,
                'timeout' => 30,
                'connect_timeout' => 15,
            ]));
        }

        return $driver;
    }

    protected function caBundlePath(): ?string
    {
        foreach ([
            base_path('certificates/cacert.pem'),
            storage_path('app/certs/cacert.pem'),
            ini_get('curl.cainfo') ?: null,
            ini_get('openssl.cafile') ?: null,
        ] as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function fail(string $message): RedirectResponse
    {
        return redirect()
            ->route('home', ['auth' => 'login'])
            ->with('error', $message);
    }
}
