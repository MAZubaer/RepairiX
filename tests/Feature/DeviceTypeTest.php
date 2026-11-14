<?php

namespace Tests\Feature;

use App\Models\ServiceRecord;
use App\Support\DeviceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DeviceTypeTest extends TestCase
{
    use RefreshDatabase;

    private function request(array $overrides = []): array
    {
        return array_merge([
            'device_type' => 'laptop',
            'phone_model' => 'Dell Inspiron 15',
            'phone_number' => '01700000000',
            'customer_problem' => 'Shuts down when it gets hot.',
        ], $overrides);
    }

    public function test_new_shops_repair_every_device_type_and_customers_see_them(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop();

        $this->assertSame(DeviceType::ALL, $shop->fresh()->deviceTypes());

        $this->actingAs($customer->user)->get('/dashboard/customer')->assertInertia(fn (Assert $page) => $page
            ->where('shops.0.device_types', DeviceType::ALL)
        );
        $this->actingAs($customer->user)->get("/customer/shops/{$shop->shop_id}")->assertInertia(fn (Assert $page) => $page
            ->where('shop.device_types', DeviceType::ALL)
        );
    }

    public function test_customer_can_request_a_laptop_repair_without_a_serial_number(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop();

        $this->actingAs($customer->user)
            ->post("/customer/shops/{$shop->shop_id}/requests", $this->request())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $record = ServiceRecord::firstOrFail();
        $this->assertSame('laptop', $record->device_type);
        $this->assertNull($record->phone_imei_number);

        $this->actingAs($customer->user)->get('/update')->assertInertia(fn (Assert $page) => $page
            ->where('records.0.device_type', 'laptop')
        );
    }

    public function test_request_is_rejected_for_a_device_the_shop_does_not_repair(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop(shopAttributes: ['device_types' => ['phone', 'tablet']]);

        $this->actingAs($customer->user)
            ->post("/customer/shops/{$shop->shop_id}/requests", $this->request())
            ->assertSessionHasErrors(['device_type' => "This shop doesn't repair laptops."]);

        $this->actingAs($customer->user)
            ->post("/customer/shops/{$shop->shop_id}/requests", $this->request(['device_type' => 'drone']))
            ->assertSessionHasErrors('device_type');

        $this->assertSame(0, ServiceRecord::count());
    }

    public function test_shop_chooses_the_devices_it_repairs(): void
    {
        $shop = $this->createShop();
        $profile = [
            'shop_name' => $shop->user->name,
            'email' => $shop->user->email,
            'phone' => '01800000000',
            'shop_address' => 'Dhaka',
        ];

        $this->actingAs($shop->user)
            ->post('/shop/edit', $profile + ['device_types' => ['accessory', 'laptop', 'laptop']])
            ->assertSessionHasNoErrors();
        $this->assertSame(['laptop', 'accessory'], $shop->fresh()->deviceTypes());

        $this->actingAs($shop->user)
            ->post('/shop/edit', $profile + ['device_types' => []])
            ->assertSessionHasErrors('device_types');
        $this->actingAs($shop->user)
            ->post('/shop/edit', $profile + ['device_types' => ['fridge']])
            ->assertSessionHasErrors('device_types.0');

        $this->assertSame(['laptop', 'accessory'], $shop->fresh()->deviceTypes());
    }
}
