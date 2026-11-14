<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_SECRET = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.stripe.webhook_secret' => self::WEBHOOK_SECRET,
            'services.stripe.monthly_price_id' => 'price_monthly',
            'services.stripe.yearly_price_id' => 'price_yearly',
        ]);
    }

    /** Send a webhook signed the same way Stripe signs it. */
    private function postSignedWebhook(array $event)
    {
        $payload = json_encode($event);
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", self::WEBHOOK_SECRET);

        return $this->call('POST', '/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $payload);
    }

    public function test_subscription_webhook_activates_shop_using_item_period_end(): void
    {
        $shop = $this->createShop(active: false, shopAttributes: ['stripe_customer_id' => 'cus_123']);
        $periodEnd = now()->addMonth()->startOfSecond();

        // Current Stripe API versions put current_period_end on the subscription items only.
        $this->postSignedWebhook([
            'id' => 'evt_1',
            'object' => 'event',
            'type' => 'customer.subscription.updated',
            'data' => ['object' => [
                'id' => 'sub_123',
                'object' => 'subscription',
                'customer' => 'cus_123',
                'status' => 'active',
                'metadata' => [],
                'items' => ['object' => 'list', 'data' => [[
                    'id' => 'si_1',
                    'object' => 'subscription_item',
                    'current_period_end' => $periodEnd->timestamp,
                    'price' => ['id' => 'price_yearly', 'object' => 'price'],
                ]]],
            ]],
        ])->assertOk();

        $shop->refresh();
        $this->assertSame('activated', $shop->subscription_status);
        $this->assertSame('yearly', $shop->subscription_plan);
        $this->assertSame('sub_123', $shop->stripe_subscription_id);
        $this->assertTrue($shop->expiry_date->equalTo($periodEnd));

        $this->actingAs($shop->user)->get(route('dashboard.shop'))->assertOk();
    }

    public function test_webhook_with_bad_signature_is_rejected(): void
    {
        $this->call('POST', '/stripe/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => 't=1,v1=invalid',
        ], '{"type":"customer.subscription.updated"}')->assertStatus(400);
    }

    public function test_admin_activation_with_past_expiry_grants_a_new_period(): void
    {
        $admin = User::factory()->admin()->create();
        $shop = $this->createShop(active: false, shopAttributes: [
            'subscription_plan' => 'monthly',
            'expiry_date' => now()->subDays(10),
        ]);

        $this->actingAs($admin)->post("/admin/shops/{$shop->shop_id}/toggle-status");

        $shop->refresh();
        $this->assertSame('activated', $shop->subscription_status);
        $this->assertTrue($shop->expiry_date->isFuture());

        // The subscription middleware must not immediately deactivate it again.
        $this->actingAs($shop->user)->get(route('dashboard.shop'))->assertOk();
        $this->assertSame('activated', $shop->fresh()->subscription_status);
    }

    public function test_non_admin_cannot_use_admin_routes(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer->user)->get('/admin/dashboard')->assertForbidden();
    }
}
