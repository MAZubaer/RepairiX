<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * Matches the custom users table: name, email, location, phone, role, password.
 * (There are no email_verified_at / remember_token columns.)
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'location' => fake()->city(),
            'phone' => fake()->numerify('01#########'),
            'role' => 'customer',
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    public function customer(): static
    {
        return $this->state(fn () => ['role' => 'customer']);
    }

    public function shop(): static
    {
        return $this->state(fn () => ['role' => 'shop']);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }
}
