<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the platform's baseline accounts: a super-admin account used to
 * administer the whole system, and a demo standard-user account useful
 * for QA and sales demos.
 *
 * Safe to re-run: accounts are upserted by email.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedSuperAdmin();
            $this->seedDemoUser();
        });
    }

    protected function seedSuperAdmin(): void
    {
        $role = Role::query()->where('slug', 'super-admin')->first();

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@calculatorhub.com'],
            [
                'name' => 'Super Admin',
                'password' => 'Password@123',
                'role_id' => $role?->id,
            ]
        );

        $user->forceFill([
            'is_active' => true,
            'is_premium' => true,
            'email_verified_at' => now(),
        ])->save();

        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    protected function seedDemoUser(): void
    {
        $role = Role::query()->where('slug', 'user')->first();

        $user = User::query()->updateOrCreate(
            ['email' => 'user@calculatorhub.com'],
            [
                'name' => 'Demo User',
                'password' => 'Password@123',
                'role_id' => $role?->id,
            ]
        );

        $user->forceFill([
            'is_active' => true,
            'email_verified_at' => now(),
        ])->save();

        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
