<?php

namespace Tests\Feature;

use App\Models\CustomerNotification;
use App\Models\ServiceRecord;
use App\Models\Shop;
use App\Models\ShopImage;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        $this->assertSame(8, ServiceRecord::count());
        $this->assertSame(3, ServiceRecord::where('status', 'in progress')->count());
        $this->assertSame(2, ShopImage::where('type', 'logo')->count());
        $this->assertSame(9, ShopImage::where('type', 'gallery')->count());
        $this->assertSame(18, CustomerNotification::count());

        Shop::all()->each(function (Shop $shop) {
            $this->assertSame('activated', $shop->subscription_status);
            $this->assertTrue($shop->expiry_date->isAfter(now()->addMonths(11)));
            $this->assertNotEmpty($shop->services_provided);
        });

        $this->assertEquals(5.0, (float) User::where('email', 'gadgetdoctor@example.com')->first()->shop->rating);
        $this->assertSame(['phone', 'tablet', 'accessory'], User::where('email', 'fixpoint@example.com')->first()->shop->deviceTypes());
        $this->assertEqualsCanonicalizing(
            ['phone', 'phone', 'laptop', 'laptop', 'tablet', 'tablet', 'accessory', 'accessory'],
            ServiceRecord::pluck('device_type')->all()
        );
        $this->assertFalse(ServiceRecord::where('created_at', '>', now())->exists());

        // The demo shop can log in and reach its dashboard without hitting the pricing page.
        $this->post('/login', ['email' => 'gadgetdoctor@example.com', 'password' => 'Demo@1234'])
            ->assertRedirect(route('dashboard.shop'));
        $this->get('/dashboard/shop')->assertOk();
    }

    public function test_extra_shops_get_photos_details_and_requests_without_touching_their_account(): void
    {
        Storage::fake('public');
        config(['filesystems.shop_images_disk' => 'public']);

        $mine = $this->createShop(shopAttributes: ['services_provided' => "Screen Replacement\r\nBattery Replacement"]);
        $mine->user->update(['password' => 'my-own-password']);
        $inactive = $this->createShop(active: false);

        $_ENV['DEMO_EXTRA_SHOPS'] = $_SERVER['DEMO_EXTRA_SHOPS'] = "{$mine->user->email}, {$inactive->user->email}";
        try {
            $this->seed(DemoSeeder::class);
            $this->seed(DemoSeeder::class);
        } finally {
            unset($_ENV['DEMO_EXTRA_SHOPS'], $_SERVER['DEMO_EXTRA_SHOPS']);
        }

        $mine->refresh();
        $this->assertSame(5, $mine->galleryImages()->count());
        $this->assertNotEmpty($mine->motto);
        $this->assertStringContainsString('Laptop motherboard', $mine->services_provided);
        $this->assertSame(4, ServiceRecord::where('shop_id', $mine->shop_id)->count());
        $this->assertEquals(5.0, (float) $mine->rating);
        $this->assertTrue(Hash::check('my-own-password', $mine->user->fresh()->password));

        $this->assertSame(0, $inactive->galleryImages()->count());
        $this->assertSame(0, ServiceRecord::where('shop_id', $inactive->shop_id)->count());
    }
}
