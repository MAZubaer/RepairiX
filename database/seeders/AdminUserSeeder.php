<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Create or update the admin account.
     *
     * Set ADMIN_PASSWORD in .env to choose the password. If it is not set, a random
     * password is generated and printed once, so no well-known default ever exists.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD');

        if (blank($password)) {
            $password = Str::password(16);
            $this->command?->warn("ADMIN_PASSWORD is not set. Generated admin password for {$email}: {$password}");
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                // Hashed by the User model's 'hashed' cast.
                'password' => $password,
                'role' => 'admin',
                'location' => env('ADMIN_LOCATION', 'Head Office'),
                'phone' => env('ADMIN_PHONE', '0000000000'),
            ]
        );
    }
}
