<?php

namespace Tests\Feature;

use App\Models\ServiceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SharedPropsAndPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_auth_prop_exposes_only_safe_fields(): void
    {
        $customer = $this->createCustomer(['name' => 'Rahim', 'email' => 'rahim@example.com']);

        $this->actingAs($customer->user)
            ->get('/about')
            ->assertInertia(fn (Assert $page) => $page
                ->component('About')
                ->where('auth.user', ['name' => 'Rahim', 'email' => 'rahim@example.com', 'role' => 'customer'])
            );
    }

    public function test_guests_get_null_auth_user(): void
    {
        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->where('auth.user', null)
        );
    }

    public function test_missing_page_renders_branded_404(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 404));
    }

    public function test_forbidden_page_renders_branded_403(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer->user)
            ->get('/admin/dashboard')
            ->assertForbidden()
            ->assertInertia(fn (Assert $page) => $page->component('Error')->where('status', 403));
    }

    public function test_sending_a_request_flashes_a_success_message(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop();

        $this->actingAs($customer->user)
            ->post("/customer/shops/{$shop->shop_id}/requests", [
                'device_type' => 'phone',
                'phone_model' => 'Pixel 8',
                'phone_number' => '01700000000',
                'phone_imei_number' => '356938035643809',
                'customer_problem' => 'Battery drains fast',
            ])
            ->assertSessionHas('success');
    }

    public function test_shop_dashboard_reports_job_counts_and_recent_requests(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop();

        $make = fn (string $status, ?float $cost = null) => ServiceRecord::create([
            'customer_id' => $customer->customer_id,
            'shop_id' => $shop->shop_id,
            'phone_number' => '01700000000',
            'phone_model' => 'Pixel 8',
            'phone_imei_number' => '356938035643809',
            'customer_problem' => 'Broken',
            'status' => $status,
            'repair_cost' => $cost,
        ]);

        $make('pending');
        $make('pending');
        $make('in progress', 500);
        $make('delivered', 1500);

        $this->actingAs($shop->user)
            ->get(route('dashboard.shop'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Shop')
                ->where('stats.pending', 2)
                ->where('stats.active', 1)
                ->where('stats.finished', 1)
                ->where('stats.earned', 1500)
                ->has('recentRequests', 4)
                ->where('subscription.plan', 'monthly')
            );
    }
}
