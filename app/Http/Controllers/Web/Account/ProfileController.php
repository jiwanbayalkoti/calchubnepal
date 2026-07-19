<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Account\UpdateProfileRequest;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {
    }

    public function edit(Request $request): View
    {
        $meta = $this->seo->buildMeta(null, [
            'title' => 'Profile Settings — AI Calculator Hub',
            'description' => 'Update your name, email, phone and password.',
            'canonical' => route('account.profile.edit'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.profile', [
            'user' => $request->user(),
            'meta' => $meta,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('account.profile.edit')
            ->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'account-deleted');
    }
}
