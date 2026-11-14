<?php

namespace Tests\Feature;

use App\Models\CustomerNotification;
use App\Models\ServiceRecord;
use App\Models\Shop;
use App\Models\ShopImage;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_active_shops_customers_and_requests_and_can_rerun(): void
    {
        Storage::fake('public');
        config(['filesystems.shop_images_disk' => 'public']);

        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertSame(2, User::where('role', 'shop')->count());
        $this->assertSame(4, User::where('role', 'customer')->count());
        $this->assertSame(4, ServiceRecord::count());
        $this->assertSame(2, ShopImage::where('type', 'logo')->count());
        $this->assertSame(9, ShopImage::where('type', 'gallery')->count());
        $this->assertSame(10, CustomerNotification::count());

        Shop::all()->each(function (Shop $shop) {
            $this->assertSame('activated', $shop->subscription_status);
            $this->assertTrue($shop->expiry_date->isAfter(now()->addMonths(11)));
            $this->assertNotEmpty($shop->services_provided);
        });

        $this->assertEquals(5.0, (float) User::where('email', 'gadgetdoctor@example.com')->first()->shop->rating);

        // The demo shop can log in and reach its dashboard without hitting the pricing page.
        $this->post('/login', ['email' => 'gadgetdoctor@example.com', 'password' => 'Demo@1234'])
            ->assertRedirect(route('dashboard.shop'));
        $this->get('/dashboard/shop')->assertOk();
    }
}
