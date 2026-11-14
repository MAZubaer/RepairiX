<?php

namespace Tests;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Pages render the Blade root view, which would otherwise need a Vite build manifest.
        $this->withoutVite();
    }

    protected function createCustomer(array $userAttributes = []): Customer
    {
        $user = User::factory()->customer()->create($userAttributes);

        return Customer::create([
            'user_id' => $user->id,
            'primary_device' => 'Pixel 8',
        ]);
    }

    protected function createShop(bool $active = true, array $shopAttributes = []): Shop
    {
        $user = User::factory()->shop()->create();

        return Shop::create(array_merge([
            'user_id' => $user->id,
            'subscription_status' => $active ? 'activated' : 'not_activated',
            'subscription_plan' => $active ? 'monthly' : null,
            'expiry_date' => $active ? now()->addMonth() : null,
            'description' => $user->location,
            'rating' => 0,
        ], $shopAttributes));
    }
}
