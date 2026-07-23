<?php

namespace Database\Seeders;

use App\Models\Advertiser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdvertiserSeeder extends Seeder
{
    public function run(): void
    {
        $roleId = Role::query()->where('slug', 'advertiser')->value('id');
        if (! $roleId) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['email' => 'advertiser@calculatorhub.com'],
            [
                'name' => 'Demo Advertiser',
                'password' => Hash::make('Password@123'),
                'role_id' => $roleId,
                'is_active' => true,
                'phone' => '9800000000',
            ]
        );

        $user->roles()->syncWithoutDetaching([$roleId]);

        Advertiser::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name' => 'Demo Ads Co',
                'contact_person' => 'Demo Advertiser',
                'phone' => '9800000000',
                'status' => Advertiser::STATUS_ACTIVE,
            ]
        );
    }
}
