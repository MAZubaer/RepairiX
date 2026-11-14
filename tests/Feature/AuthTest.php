<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_lands_on_dashboard(): void
    {
        $response = $this->post('/register/customer', [
            'name' => 'Rahim',
            'email' => 'rahim@example.com',
            'phone' => '01700000000',
            'location' => 'Dhaka',
            'primary_device' => 'iPhone 13',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard.customer'));
        $this->assertAuthenticated();

        $user = User::where('email', 'rahim@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertTrue(Customer::where('user_id', $user->id)->exists());
    }

    public function test_shop_registration_creates_inactive_shop_and_redirects_to_pricing(): void
    {
        $response = $this->post('/register/shop', [
            'shop_name' => 'Fix It',
            'email' => 'fixit@example.com',
            'phone' => '01800000000',
            'shop_address' => 'Mirpur',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('shop.pricing'));

        $user = User::where('email', 'fixit@example.com')->firstOrFail();
        $shop = Shop::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('not_activated', $shop->subscription_status);
    }

    public function test_validation_errors_are_shared_as_whole_messages(): void
    {
        $this->createCustomer(['email' => 'taken@example.com']);

        $this->from('/register/customer')
            ->post('/register/customer', [
                'name' => 'Someone',
                'email' => 'taken@example.com',
                'phone' => '01700000000',
                'location' => 'Dhaka',
                'primary_device' => 'Phone',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect('/register/customer');

        // The Vue pages render errors.<field> directly, so each one must be a full string.
        $this->get('/register/customer')->assertInertia(fn (Assert $page) => $page
            ->component('RegisterCustomer')
            ->where('errors.email', 'You have already an account with this email')
        );
    }

    public function test_login_redirects_each_role_to_its_home(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = $this->createShop(active: true);
        $customer = $this->createCustomer();

        $cases = [
            [$admin, route('admin.dashboard')],
            [$shop->user, route('dashboard.shop')],
            [$customer->user, route('dashboard.customer')],
        ];

        foreach ($cases as [$user, $expected]) {
            $this->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect($expected);
            $this->post('/logout');
        }
    }

    public function test_inactive_shop_is_sent_to_pricing_after_login(): void
    {
        $shop = $this->createShop(active: false);

        $this->post('/login', ['email' => $shop->user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard.shop'));

        $this->get(route('dashboard.shop'))->assertRedirect(route('shop.pricing'));
    }

    public function test_login_is_throttled_after_repeated_failures(): void
    {
        $customer = $this->createCustomer();
        RateLimiter::clear(strtolower($customer->user->email).'|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $customer->user->email, 'password' => 'wrong'])
                ->assertSessionHasErrors(['email' => 'Invalid email or password.']);
        }

        // Even the right password is refused while locked out.
        $this->post('/login', ['email' => $customer->user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
    }

    public function test_logged_in_users_are_redirected_away_from_guest_pages(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer->user)
            ->get('/register/shop')
            ->assertRedirect(route('dashboard.customer'));

        $this->actingAs($customer->user)
            ->get('/login')
            ->assertRedirect(route('dashboard.customer'));
    }
}
