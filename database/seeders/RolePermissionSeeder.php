<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the full RBAC matrix: every module gets the standard action set,
 * and the four baseline roles (super-admin, admin, editor, user) are
 * wired up with the permission sets appropriate to their scope.
 *
 * Safe to re-run: permissions and roles are upserted by their unique
 * slug, so this seeder never creates duplicates.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    protected const MODULES = [
        'calculators' => 'Calculators',
        'categories' => 'Calculator Categories',
        'users' => 'Users',
        'roles' => 'Roles & Permissions',
        'blog' => 'Blog',
        'advertisements' => 'Advertisements',
        'advertisers' => 'Advertisers',
        'subscriptions' => 'Subscriptions',
        'api_keys' => 'API Keys',
        'feedback' => 'Feedback',
        'contact_messages' => 'Contact Messages',
        'settings' => 'Settings',
        'ai_prompts' => 'AI Prompts',
        'analytics' => 'Analytics',
        'seo_pages' => 'SEO Pages',
    ];

    /**
     * @var array<string, string>
     */
    protected const ACTIONS = [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'export' => 'Export',
        'print' => 'Print',
        'approve' => 'Approve',
    ];

    /**
     * Permissions outside the standard module/action matrix that the
     * application checks directly (e.g. admin panel gate).
     *
     * @var array<int, array{module: string, action: string, name: string, description: string}>
     */
    protected const EXTRA_PERMISSIONS = [
        [
            'module' => 'admin',
            'action' => 'dashboard.view',
            'name' => 'Access Admin Dashboard',
            'description' => 'Allows a non-admin role to access the admin panel dashboard.',
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $permissions = $this->seedPermissions();

            $this->seedSuperAdmin($permissions);
            $this->seedAdmin($permissions);
            $this->seedEditor($permissions);
            $this->seedUser();
            $this->seedAdvertiser();
        });
    }

    /**
     * @return array<string, Permission>
     */
    protected function seedPermissions(): array
    {
        $permissions = [];

        foreach (self::MODULES as $module => $moduleLabel) {
            foreach (self::ACTIONS as $action => $actionLabel) {
                $slug = "{$module}.{$action}";

                $permissions[$slug] = Permission::query()->updateOrCreate(
                    ['module' => $module, 'action' => $action],
                    [
                        'name' => "{$actionLabel} {$moduleLabel}",
                        'slug' => $slug,
                        'guard_name' => 'web',
                        'description' => "Allows the user to {$action} {$moduleLabel}.",
                    ]
                );
            }
        }

        foreach (self::EXTRA_PERMISSIONS as $extra) {
            $slug = "{$extra['module']}.{$extra['action']}";

            $permissions[$slug] = Permission::query()->updateOrCreate(
                ['module' => $extra['module'], 'action' => $extra['action']],
                [
                    'name' => $extra['name'],
                    'slug' => $slug,
                    'guard_name' => 'web',
                    'description' => $extra['description'],
                ]
            );
        }

        return $permissions;
    }

    /**
     * @param  array<string, Permission>  $permissions
     */
    protected function seedSuperAdmin(array $permissions): void
    {
        $role = Role::query()->updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'guard_name' => 'web',
                'description' => 'Full, unrestricted access to every module and setting in the platform.',
                'is_system' => true,
            ]
        );

        $role->permissions()->sync(collect($permissions)->pluck('id')->all());
    }

    /**
     * @param  array<string, Permission>  $permissions
     */
    protected function seedAdmin(array $permissions): void
    {
        $role = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'guard_name' => 'web',
                'description' => 'Company-level administrator with access to nearly every module, excluding role deletion.',
                'is_system' => true,
            ]
        );

        $excluded = ['roles.delete'];

        $ids = collect($permissions)
            ->reject(fn (Permission $permission) => in_array($permission->slug, $excluded, true))
            ->pluck('id')
            ->all();

        $role->permissions()->sync($ids);
    }

    /**
     * @param  array<string, Permission>  $permissions
     */
    protected function seedEditor(array $permissions): void
    {
        $role = Role::query()->updateOrCreate(
            ['slug' => 'editor'],
            [
                'name' => 'Editor',
                'guard_name' => 'web',
                'description' => 'Content editor with create/edit access to calculators, blog posts and SEO pages.',
                'is_system' => true,
            ]
        );

        $slugs = [
            'blog.view', 'blog.create', 'blog.edit',
            'calculators.view', 'calculators.create', 'calculators.edit',
            'seo_pages.view', 'seo_pages.create', 'seo_pages.edit',
        ];

        $ids = collect($permissions)
            ->filter(fn (Permission $permission) => in_array($permission->slug, $slugs, true))
            ->pluck('id')
            ->all();

        $role->permissions()->sync($ids);
    }

    protected function seedUser(): void
    {
        $role = Role::query()->updateOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'guard_name' => 'web',
                'description' => 'Standard registered user with no administrative permissions.',
                'is_system' => true,
            ]
        );

        $role->permissions()->sync([]);
    }

    protected function seedAdvertiser(): void
    {
        Role::query()->updateOrCreate(
            ['slug' => 'advertiser'],
            [
                'name' => 'Advertiser',
                'guard_name' => 'web',
                'description' => 'Read-only advertiser portal access to own ads, reports, and profile.',
                'is_system' => true,
            ]
        )->permissions()->sync([]);
    }
}
