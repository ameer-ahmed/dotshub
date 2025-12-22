<?php

namespace Database\Seeders;

use App\Enums\SystemStatus;
use App\Models\Tenant;
use App\Models\Plan;
use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
//            LaratrustSeeder::class,
//            MerchantSeeder::class,
        ]);

//        $merchant1 = System::query()->create([
//            'name' => 'Merchant 1',
//            'description' => 'Merchant 1 Description',
//            'status' => SystemStatus::ACTIVE,
//        ]);
//
//        $merchant1->domains()->create([
//            'domain' => 'm1.tracer.test'
//        ]);
//
//        $merchant2 = System::query()->create([
//            'name' => 'Merchant 2',
//            'description' => 'Merchant 2 Description',
//            'status' => SystemStatus::ACTIVE,
//        ]);
//
//        $merchant2->domains()->create([
//            'domain' => 'm2.tracer.test'
//        ]);

//        $user = User::create([
//           'name' => 'store',
//           'email' => 'store@hub.com',
//           'password' => '123123',
//           'merchant_id' => 1,
//        ]);
//
//        $user->addRole('system_admin');
//
//        User::create([
//            'name' => 'store',
//            'email' => 'store1@hub.com',
//            'password' => '123123',
//            'merchant_id' => 1,
//        ]);
//
//        $admin = Admin::create([
//            'name' => 'superadmin',
//            'email' => 'admin@admin.com',
//            'password' => '123123',
//        ]);
//        $admin->addRole('super_admin');
//
        Plan::query()->create([
            'name' => [
                'ar' => 'خطة #0',
                'en' => 'Plan #0',
            ],
            'description' => [
                'ar' => 'خطة تجريبية',
                'en' => 'Trial Plan',
            ],
            'price' => 0,
            'is_trial' => true,
            'is_active' => true,
        ]);

        Plan::factory(3)->create();

    }
}
