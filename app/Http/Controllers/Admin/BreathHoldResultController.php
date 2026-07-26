<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Models\BreathHoldResult;
use App\Services\BreathHold\BreathHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BreathHoldResultController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected BreathHoldService $breathHold)
    {
    }

    public function index(): View
    {
        $stats = $this->breathHold->adminStats();

        return view('admin.breath-hold.index', compact('stats'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = BreathHoldResult::query()->with('user:id,name,email');

        if ($band = $request->input('band')) {
            $query->where('band', $band);
        }

        if ($request->boolean('certified_only')) {
            $query->whereNotNull('certificate_code');
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('certificate_code', 'like', "%{$search}%")
                    ->orWhere('band', 'like', "%{$search}%")
                    ->orWhere('claim_token', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
            $request->merge(['search' => ['value' => '']]);
        }

        return $this->toDataTableResponse(
            $request,
            $query,
            [],
            ['id', 'user_id', 'duration_seconds', 'band', 'certificate_code', 'created_at'],
            function (BreathHoldResult $row) {
                return [
                    'id' => $row->id,
                    'user' => $row->user
                        ? e($row->user->name).'<br><small class="text-muted">'.e($row->user->email).'</small>'
                        : '<span class="text-muted">Guest (unclaimed)</span>',
                    'duration' => e($row->formattedDuration()),
                    'band' => '<span class="badge badge-'.match ($row->band) {
                        'poor' => 'danger',
                        'medium' => 'warning',
                        'healthy' => 'success',
                        default => 'secondary',
                    }.'">'.e($row->bandLabel()).'</span>',
                    'certificate' => $row->certificate_code
                        ? '<code>'.e($row->certificate_code).'</code>'
                        : '<span class="text-muted">—</span>',
                    'played_at' => $row->created_at?->format('M d, Y H:i'),
                ];
            }
        );
    }
}
