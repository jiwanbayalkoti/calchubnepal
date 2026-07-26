<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Admin\UserRegistered;
use App\Services\Admin\AdminNotifier;
use App\Services\BreathHold\BreathHoldService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected AdminNotifier $notifier,
        protected BreathHoldService $breathHold,
    ) {
    }

    /**
     * Display the registration view (full page fallback / redirect to modal on home).
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->boolean('page')) {
            return redirect()->route('home', ['auth' => 'register']);
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        $this->notifier->notify(new UserRegistered($user, 'email'));

        Auth::login($user);

        $claimed = $this->breathHold->claimPendingFromSession($request, $user);
        $redirectTo = $claimed
            ? route('home', absolute: false).'#breath-hold'
            : route('account.dashboard', absolute: false);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $claimed
                    ? 'Account created. Your breath-hold certificate is ready.'
                    : 'Account created successfully.',
                'redirect' => $redirectTo,
                'certificate_claimed' => (bool) $claimed,
                'certificate_url' => $claimed ? route('breath-hold.certificate', $claimed) : null,
            ]);
        }

        return redirect($redirectTo);
    }
}
