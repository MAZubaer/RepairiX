<?php

namespace Tests\Feature;

use App\Mail\ServiceCompletedMail;
use App\Mail\ServiceInProgressMail;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\ServiceRecord;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ServiceRequestLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function submitRequest(Customer $customer, Shop $shop): ServiceRecord
    {
        $this->actingAs($customer->user)
            ->post("/customer/shops/{$shop->shop_id}/requests", [
                'device_type' => 'phone',
                'phone_model' => 'Galaxy S23',
                'phone_number' => '01711111111',
                'phone_imei_number' => '356938035643809',
                'customer_problem' => 'Screen cracked',
            ])
            ->assertRedirect(route('customer.shops.show', $shop->shop_id));

        return ServiceRecord::latest('service_id')->firstOrFail();
    }

    private function shopAction(Shop $shop, ServiceRecord $record, string $action)
    {
        return $this->actingAs($shop->user)
            ->post("/shop/customers/{$record->service_id}/status", ['action' => $action]);
    }

    public function test_full_repair_lifecycle_from_request_to_rating(): void
    {
        Mail::fake();

        $customer = $this->createCustomer();
        $shop = $this->createShop();
        $record = $this->submitRequest($customer, $shop);
        $this->assertSame('pending', $record->status);

        $this->shopAction($shop, $record, 'accept');
        $this->shopAction($shop, $record, 'start_progress');
        Mail::assertQueued(ServiceInProgressMail::class);

        // Completing requires a diagnosis and a cost first.
        $this->shopAction($shop, $record, 'mark_complete')->assertSessionHasErrors('status');
        $this->actingAs($shop->user)->post("/shop/customers/{$record->service_id}/problem", ['shop_problem' => 'Broken display']);
        $this->actingAs($shop->user)->post("/shop/customers/{$record->service_id}/cost", ['repair_cost' => '2500']);
        $this->shopAction($shop, $record, 'mark_complete')->assertSessionHasNoErrors();
        Mail::assertQueued(ServiceCompletedMail::class);

        $this->shopAction($shop, $record, 'sent_from_shop');

        $this->actingAs($customer->user)->post("/customer/updates/{$record->service_id}/accept");
        $this->actingAs($customer->user)->post("/customer/updates/{$record->service_id}/rating", ['rating' => 4]);

        $record->refresh();
        $this->assertSame('delivered', $record->status);
        $this->assertSame(4, (int) $record->rating);
        $this->assertEquals(4.0, (float) $shop->fresh()->rating);
        // accepted, in progress, completed, sent from shop (the refused completion adds none).
        $this->assertSame(4, CustomerNotification::where('service_id', $record->service_id)->count());
    }

    public function test_problem_and_cost_are_locked_once_request_is_finished(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop();
        $record = $this->submitRequest($customer, $shop);
        $record->update(['status' => 'delivered', 'repair_cost' => 1000, 'shop_problem' => 'Battery']);

        $this->actingAs($shop->user)
            ->post("/shop/customers/{$record->service_id}/cost", ['repair_cost' => '1'])
            ->assertSessionHasErrors('status');
        $this->actingAs($shop->user)
            ->post("/shop/customers/{$record->service_id}/problem", ['shop_problem' => 'Changed'])
            ->assertSessionHasErrors('status');

        $record->refresh();
        $this->assertEquals(1000, (float) $record->repair_cost);
        $this->assertSame('Battery', $record->shop_problem);
    }

    public function test_shop_cannot_touch_another_shops_request(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop();
        $otherShop = $this->createShop();
        $record = $this->submitRequest($customer, $shop);

        $this->shopAction($otherShop, $record, 'accept')->assertForbidden();
        $this->assertSame('pending', $record->fresh()->status);
    }

    public function test_customer_cannot_request_from_inactive_shop(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop(active: false);

        $this->actingAs($customer->user)
            ->post("/customer/shops/{$shop->shop_id}/requests", [
                'device_type' => 'phone',
                'phone_model' => 'Galaxy S23',
                'phone_number' => '01711111111',
                'phone_imei_number' => '356938035643809',
                'customer_problem' => 'Screen cracked',
            ])
            ->assertSessionHasErrors('server');

        $this->assertSame(0, ServiceRecord::count());
    }
}
