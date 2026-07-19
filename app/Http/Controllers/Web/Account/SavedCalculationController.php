<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Account\StoreSavedCalculationRequest;
use App\Models\Calculator;
use App\Models\SavedCalculation;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedCalculationController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $saved = $user->savedCalculations()
            ->with('calculator:id,title,slug,icon')
            ->latest()
            ->paginate(12);

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Saved Calculations — AI Calculator Hub',
            'description' => 'Your saved calculator results.',
            'canonical' => route('account.saved.index'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.saved.index', [
            'saved' => $saved,
            'savedCount' => $user->savedCalculations()->count(),
            'savedLimit' => $user->savedCalculationsLimit(),
            'meta' => $meta,
        ]);
    }

    public function show(Request $request, SavedCalculation $saved): View
    {
        abort_unless($saved->user_id === $request->user()->id, 403);

        $saved->load('calculator:id,title,slug,icon');

        $meta = $this->seo->buildMeta(null, [
            'title' => $saved->title.' — Saved Calculation',
            'description' => 'View a saved calculator result.',
            'canonical' => route('account.saved.show', $saved),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.saved.show', [
            'saved' => $saved,
            'meta' => $meta,
        ]);
    }

    public function store(StoreSavedCalculationRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user->canSaveMoreCalculations()) {
            $limit = $user->savedCalculationsLimit();
            $message = "Free plan allows {$limit} saved calculations. Upgrade to Premium for unlimited saves.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'upgrade_url' => route('pricing'),
                ], 403);
            }

            return back()->with('error', $message);
        }

        $calculator = Calculator::query()
            ->where('slug', $request->validated('calculator_slug'))
            ->where('is_active', true)
            ->firstOrFail();

        $saved = SavedCalculation::query()->create([
            'user_id' => $user->id,
            'calculator_id' => $calculator->id,
            'title' => $request->validated('title'),
            'inputs' => $request->validated('inputs'),
            'outputs' => $request->validated('outputs'),
            'notes' => $request->validated('notes'),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Calculation saved.',
                'data' => [
                    'id' => $saved->id,
                    'url' => route('account.saved.show', $saved),
                ],
            ], 201);
        }

        return redirect()
            ->route('account.saved.show', $saved)
            ->with('status', 'calculation-saved');
    }

    public function destroy(Request $request, SavedCalculation $saved): RedirectResponse
    {
        abort_unless($saved->user_id === $request->user()->id, 403);

        $saved->delete();

        return redirect()
            ->route('account.saved.index')
            ->with('status', 'saved-deleted');
    }
}
