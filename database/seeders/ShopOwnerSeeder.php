<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ShopOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $email = env('SHOP_OWNER_EMAIL', 'owner@example.com');
        $password = env('SHOP_OWNER_PASSWORD', 'ownerpass123');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SHOP_OWNER_NAME', 'Shop Owner'),
                'password' => Hash::make($password),
                'role' => 'shop',
                'location' => env('SHOP_OWNER_LOCATION', 'Main Street Shop'),
                'phone' => env('SHOP_OWNER_PHONE', '1111111111'),
            ]
        );
    }
}
