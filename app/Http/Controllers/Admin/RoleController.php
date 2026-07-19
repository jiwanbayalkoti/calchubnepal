<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        $permissions = Permission::query()->orderBy('module')->orderBy('action')->get()->groupBy('module');

        return view('admin.roles.index', compact('permissions'));
    }

    public function data(Request $request): JsonResponse
    {
        $query = Role::query()->withCount(['users', 'permissions']);

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['name', 'slug'],
            orderableColumns: ['name', 'is_system', 'permissions_count', 'users_count', 'created_at'],
            transform: function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'is_system' => (bool) $role->is_system,
                    'permissions_count' => $role->permissions_count,
                    'users_count' => $role->users_count,
                ];
            }
        );
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $data = $request->safe()->except('permissions');
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['created_by'] = $request->user()?->id;

        $role = Role::create($data);
        $role->permissions()->sync($request->input('permissions', []));

        $this->activityLog->log('create', 'roles', $role, ['name' => $role->name]);

        return response()->json(['message' => 'Role created successfully.', 'data' => $role], 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions:id')->findOrFail($id);

        $data = $role->toArray();
        $data['permissions'] = $role->permissions->pluck('id');

        return response()->json(['data' => $data]);
    }

    public function update(RoleRequest $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            return response()->json(['message' => 'System roles cannot be modified.'], 422);
        }

        $data = $request->safe()->except('permissions');
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['updated_by'] = $request->user()?->id;

        $role->update($data);
        $role->permissions()->sync($request->input('permissions', []));

        $this->activityLog->log('update', 'roles', $role, ['name' => $role->name]);

        return response()->json(['message' => 'Role updated successfully.', 'data' => $role]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if ($role->is_system) {
            return response()->json(['message' => 'System roles cannot be deleted.'], 422);
        }

        $name = $role->name;
        $role->update(['deleted_by' => $request->user()?->id]);
        $role->delete();

        $this->activityLog->log('delete', 'roles', null, ['name' => $name]);

        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
