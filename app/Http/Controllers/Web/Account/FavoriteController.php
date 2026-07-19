<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\Calculator;
use App\Models\CalculatorFavorite;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $favorites = $request->user()
            ->favorites()
            ->with('calculator.category')
            ->latest()
            ->paginate(12);

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Favorite Calculators — AI Calculator Hub',
            'description' => 'Your saved favorite calculators for quick access.',
            'canonical' => route('account.favorites.index'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.favorites.index', [
            'favorites' => $favorites,
            'meta' => $meta,
        ]);
    }

    public function toggle(Request $request, Calculator $calculator): JsonResponse|RedirectResponse
    {
        abort_unless($calculator->is_active, 404);

        $user = $request->user();
        $existing = CalculatorFavorite::query()
            ->where('user_id', $user->id)
            ->where('calculator_id', $calculator->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
            $message = 'Removed from favorites.';
        } else {
            CalculatorFavorite::query()->create([
                'user_id' => $user->id,
                'calculator_id' => $calculator->id,
            ]);
            $favorited = true;
            $message = 'Added to favorites.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'favorited' => $favorited,
                'message' => $message,
            ]);
        }

        return back()->with('status', $favorited ? 'favorite-added' : 'favorite-removed');
    }

    public function destroy(Request $request, CalculatorFavorite $favorite): RedirectResponse
    {
        abort_unless($favorite->user_id === $request->user()->id, 403);

        $favorite->delete();

        return back()->with('status', 'favorite-removed');
    }
}
