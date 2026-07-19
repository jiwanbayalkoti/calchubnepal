<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        $roles = Role::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.users.index', compact('roles'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = User::query()->with('primaryRole');

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['name', 'email', 'phone'],
            orderableColumns: ['name', 'email', 'role_id', 'is_active', 'is_premium', 'created_at'],
            transform: function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->primaryRole?->name,
                    'is_active' => (bool) $user->is_active,
                    'is_premium' => (bool) $user->is_premium,
                    'premium_expires_at' => $user->premium_expires_at?->format('Y-m-d'),
                    'created_at' => $user->created_at?->format('Y-m-d'),
                ];
            }
        );
    }

    public function store(UserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = $request->user()?->id;

        $user = User::create($data);

        $this->activityLog->log('create', 'users', $user, ['email' => $user->email]);

        return response()->json(['message' => 'User created successfully.', 'data' => $user], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json([
            'data' => array_merge($user->toArray(), [
                'premium_expires_at' => $user->premium_expires_at?->format('Y-m-d'),
            ]),
        ]);
    }

    public function update(UserRequest $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $data['updated_by'] = $request->user()?->id;

        $user->update($data);

        $this->activityLog->log('update', 'users', $user, ['email' => $user->email]);

        return response()->json(['message' => 'User updated successfully.', 'data' => $user]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()?->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $email = $user->email;
        $user->update(['deleted_by' => $request->user()?->id]);
        $user->delete();

        $this->activityLog->log('delete', 'users', null, ['email' => $email]);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);

        return response()->json(['message' => 'Status updated successfully.', 'data' => $user]);
    }
}
