<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\CalculationHistory;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $histories = $request->user()
            ->histories()
            ->with('calculator:id,title,slug,icon')
            ->latest('created_at')
            ->paginate(15);

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Calculation History — AI Calculator Hub',
            'description' => 'Browse your recent calculator results.',
            'canonical' => route('account.history.index'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.history.index', [
            'histories' => $histories,
            'meta' => $meta,
        ]);
    }

    public function destroy(Request $request, CalculationHistory $history): RedirectResponse
    {
        abort_unless($history->user_id === $request->user()->id, 403);

        $history->delete();

        return back()->with('status', 'history-deleted');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->user()->histories()->delete();

        return redirect()
            ->route('account.history.index')
            ->with('status', 'history-cleared');
    }
}
