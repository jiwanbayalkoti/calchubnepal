<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters: roles/permissions must exist before users are
     * attached to them, and categories must exist before calculators
     * reference them.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
            SubscriptionPlanSeeder::class,
            AiPromptSeeder::class,
            CalculatorSeeder::class,
            BlogSeeder::class,
            AdvertisementSeeder::class,
            SeoPageSeeder::class,
        ]);
    }
}
