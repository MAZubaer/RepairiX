<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShopOwnerSeeder extends Seeder
{
    /**
     * Create or update a demo shop account (user + shop row).
     *
     * Set SHOP_OWNER_PASSWORD in .env to choose the password. If it is not set, a random
     * password is generated and printed once.
     */
    public function run(): void
    {
        $email = env('SHOP_OWNER_EMAIL', 'owner@example.com');
        $password = env('SHOP_OWNER_PASSWORD');

        if (blank($password)) {
            $password = Str::password(16);
            $this->command?->warn("SHOP_OWNER_PASSWORD is not set. Generated shop owner password for {$email}: {$password}");
        }

        $location = env('SHOP_OWNER_LOCATION', 'Main Street Shop');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SHOP_OWNER_NAME', 'Shop Owner'),
                // Hashed by the User model's 'hashed' cast.
                'password' => $password,
                'role' => 'shop',
                'location' => $location,
                'phone' => env('SHOP_OWNER_PHONE', '1111111111'),
            ]
        );

        Shop::firstOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_status' => 'not_activated',
                'expiry_date' => null,
                'description' => $location,
                'rating' => 0,
            ]
        );
    }
}
