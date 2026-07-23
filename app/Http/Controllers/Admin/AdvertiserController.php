<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvertiserRequest;
use App\Models\Advertiser;
use App\Models\Role;
use App\Models\User;
use App\Services\Activity\ActivityLogService;
use App\Services\Media\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdvertiserController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(
        protected ActivityLogService $activityLog,
        protected FileUploadService $uploads,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Advertiser::class);

        return view('admin.advertisers.index');
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Advertiser::class);

        $query = Advertiser::query()->with(['user:id,email,is_active', 'advertisements']);

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['company_name', 'contact_person', 'phone'],
            orderableColumns: ['company_name', 'contact_person', 'status', 'created_at'],
            transform: function (Advertiser $advertiser) {
                return [
                    'id' => $advertiser->id,
                    'company_name' => $advertiser->company_name,
                    'contact_person' => $advertiser->contact_person,
                    'email' => $advertiser->user?->email,
                    'phone' => $advertiser->phone,
                    'status' => $advertiser->status,
                    'ads_count' => $advertiser->advertisements->count(),
                    'logo_url' => $advertiser->logo_url,
                    'created_at' => $advertiser->created_at?->format('Y-m-d'),
                ];
            }
        );
    }

    public function store(AdvertiserRequest $request): JsonResponse
    {
        $this->authorize('create', Advertiser::class);

        $data = $request->validated();

        $advertiser = DB::transaction(function () use ($request, $data) {
            $roleId = Role::query()->where('slug', 'advertiser')->value('id');

            $user = User::query()->create([
                'name' => $data['contact_person'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role_id' => $roleId,
                'is_active' => ($data['status'] ?? 'active') === 'active',
                'created_by' => $request->user()?->id,
            ]);

            if ($roleId) {
                $user->roles()->syncWithoutDetaching([$roleId]);
            }

            $logo = null;
            if ($request->hasFile('logo')) {
                $logo = $this->uploads->storePublic($request->file('logo'), 'advertisers/logos');
            }

            return Advertiser::query()->create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'contact_person' => $data['contact_person'],
                'phone' => $data['phone'] ?? null,
                'logo' => $logo,
                'status' => $data['status'] ?? Advertiser::STATUS_ACTIVE,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);
        });

        $this->activityLog->log('create', 'advertisers', $advertiser, [
            'company' => $advertiser->company_name,
            'email' => $data['email'],
        ]);

        return response()->json([
            'message' => 'Advertiser created successfully.',
            'data' => $advertiser->load('user:id,email'),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $advertiser = Advertiser::query()->with('user:id,email,is_active')->findOrFail($id);
        $this->authorize('view', $advertiser);

        return response()->json([
            'data' => array_merge($advertiser->toArray(), [
                'email' => $advertiser->user?->email,
                'logo_url' => $advertiser->logo_url,
            ]),
        ]);
    }

    public function update(AdvertiserRequest $request, int $id): JsonResponse
    {
        $advertiser = Advertiser::query()->with('user')->findOrFail($id);
        $this->authorize('update', $advertiser);

        $data = $request->validated();

        DB::transaction(function () use ($request, $advertiser, $data) {
            $advertiser->fill([
                'company_name' => $data['company_name'],
                'contact_person' => $data['contact_person'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? $advertiser->status,
                'notes' => $data['notes'] ?? $advertiser->notes,
                'updated_by' => $request->user()?->id,
            ]);

            if ($request->hasFile('logo')) {
                if ($advertiser->logo) {
                    $this->uploads->deletePublic($advertiser->logo);
                }
                $advertiser->logo = $this->uploads->storePublic($request->file('logo'), 'advertisers/logos');
            }

            $advertiser->save();

            if ($advertiser->user) {
                $userData = [
                    'name' => $data['contact_person'],
                    'phone' => $data['phone'] ?? $advertiser->user->phone,
                    'is_active' => ($data['status'] ?? $advertiser->status) === 'active',
                    'updated_by' => $request->user()?->id,
                ];

                if (! empty($data['password'])) {
                    $userData['password'] = Hash::make($data['password']);
                }

                // Email intentionally not updatable from this form for stability.
                $advertiser->user->update($userData);
            }
        });

        $this->activityLog->log('update', 'advertisers', $advertiser, [
            'company' => $advertiser->company_name,
        ]);

        return response()->json([
            'message' => 'Advertiser updated successfully.',
            'data' => $advertiser->fresh()->load('user:id,email'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $advertiser = Advertiser::query()->with('user')->findOrFail($id);
        $this->authorize('delete', $advertiser);

        $name = $advertiser->company_name;

        if ($advertiser->logo) {
            $this->uploads->deletePublic($advertiser->logo);
        }

        $user = $advertiser->user;
        $advertiser->delete();

        if ($user) {
            $user->update(['is_active' => false]);
        }

        $this->activityLog->log('delete', 'advertisers', null, ['company' => $name]);

        return response()->json(['message' => 'Advertiser deleted successfully.']);
    }
}
