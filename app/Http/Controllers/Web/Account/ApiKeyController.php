<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\Qr\QrEntitlementService;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function __construct(
        protected QrEntitlementService $entitlements,
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $keys = $user->apiKeys()->latest()->get();

        return view('account.api-keys.index', [
            'user' => $user,
            'keys' => $keys,
            'maxKeys' => $this->entitlements->maxApiKeys($user),
            'plainKey' => session('api_key_plain'),
            'meta' => $this->seo->buildMeta(null, [
                'title' => 'API Keys — CalchubNepal',
                'canonical' => route('account.api-keys.index'),
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->apiKeys()->count() >= $this->entitlements->maxApiKeys($user)) {
            return back()->with('error', 'API key limit reached. Upgrade your plan for more keys.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'rate_limit_per_minute' => ['nullable', 'integer', 'min:10', 'max:600'],
        ]);

        $pair = ApiKey::generateKeyPair();
        ApiKey::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'key' => $pair['hashed'],
            'key_prefix' => $pair['prefix'],
            'rate_limit_per_minute' => (int) ($data['rate_limit_per_minute'] ?? 60),
            'is_active' => true,
        ]);

        return redirect()
            ->route('account.api-keys.index')
            ->with('status', 'api-key-created')
            ->with('api_key_plain', $pair['plainText']);
    }

    public function destroy(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_unless((int) $apiKey->user_id === (int) $request->user()->id, 403);
        $apiKey->delete();

        return back()->with('status', 'api-key-revoked');
    }

    public function toggle(Request $request, ApiKey $apiKey): RedirectResponse
    {
        abort_unless((int) $apiKey->user_id === (int) $request->user()->id, 403);
        $apiKey->update(['is_active' => ! $apiKey->is_active]);

        return back()->with('status', 'api-key-updated');
    }
}
