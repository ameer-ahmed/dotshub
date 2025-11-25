<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'store',
            'email' => 'store@hub.com',
            'password' => '123123',
        ]);

        $user->addRole('merchant_admin');

        User::create([
            'name' => 'store',
            'email' => 'store1@hub.com',
            'password' => '123123',
        ]);
    }
}
