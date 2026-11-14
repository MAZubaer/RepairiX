<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ShopOwnerSeeder::class,
        ]);

        // A demo customer for local development only (password: "password").
        if (app()->isLocal()) {
            $user = User::firstOrCreate(
                ['email' => 'test@example.com'],
                User::factory()->customer()->raw(['name' => 'Test User', 'email' => 'test@example.com']),
            );

            Customer::firstOrCreate(
                ['user_id' => $user->id],
                ['primary_device' => 'Demo Phone'],
            );
        }
    }
}
