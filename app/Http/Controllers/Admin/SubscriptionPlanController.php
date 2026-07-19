<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubscriptionPlanRequest;
use App\Models\SubscriptionPlan;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        return view('admin.subscription-plans.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = SubscriptionPlan::query()->withCount('subscriptions');

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['name', 'slug'],
            orderableColumns: ['name', 'price', 'billing_period', 'is_active', 'subscriptions_count', 'created_at'],
            transform: function (SubscriptionPlan $plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'billing_period' => $plan->billing_period,
                    'is_active' => (bool) $plan->is_active,
                    'subscriptions_count' => $plan->subscriptions_count,
                ];
            }
        );
    }

    public function store(SubscriptionPlanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;

        $plan = SubscriptionPlan::create($data);

        $this->activityLog->log('create', 'subscription_plans', $plan, ['name' => $plan->name]);

        return response()->json(['message' => 'Subscription plan created successfully.', 'data' => $plan], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => SubscriptionPlan::findOrFail($id)]);
    }

    public function update(SubscriptionPlanRequest $request, int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $plan->update($data);

        $this->activityLog->log('update', 'subscription_plans', $plan, ['name' => $plan->name]);

        return response()->json(['message' => 'Subscription plan updated successfully.', 'data' => $plan]);
    }

    public function destroy(int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);

        if ($plan->subscriptions()->exists()) {
            return response()->json(['message' => 'This plan has active subscribers and cannot be deleted.'], 422);
        }

        $name = $plan->name;
        $plan->delete();

        $this->activityLog->log('delete', 'subscription_plans', null, ['name' => $name]);

        return response()->json(['message' => 'Subscription plan deleted successfully.']);
    }
}
