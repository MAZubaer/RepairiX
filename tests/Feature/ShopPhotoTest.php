<?php

namespace Tests\Feature;

use App\Http\Controllers\ShopPhotoController;
use App\Models\Shop;
use App\Models\ShopImage;
use App\Services\Images\CloudinaryGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery;
use Tests\TestCase;

class ShopPhotoTest extends TestCase
{
    use RefreshDatabase;

    /** A real 1x1 PNG, so MIME detection works without the GD extension. */
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        config(['filesystems.shop_images_disk' => 'public']);
    }

    private function png(string $name = 'photo.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::PNG));
    }

    private function addPhotos(Shop $shop, int $count): array
    {
        return collect(range(1, $count))->map(fn ($i) => ShopImage::create([
            'shop_id' => $shop->shop_id,
            'image_path' => "shop_images/gallery/existing-{$i}.png",
            'disk' => 'public',
            'type' => 'gallery',
            'sort_order' => $i,
        ]))->all();
    }

    public function test_shop_uploads_photos_to_local_storage_in_order(): void
    {
        $shop = $this->createShop();

        $this->actingAs($shop->user)
            ->post('/shop/photos', ['photos' => [$this->png('a.png'), $this->png('b.png')]])
            ->assertSessionHas('success', '2 photos added.');

        $photos = $shop->galleryImages()->get();
        $this->assertCount(2, $photos);
        $this->assertSame([1, 2], $photos->pluck('sort_order')->all());
        $this->assertTrue($photos->every(fn ($p) => $p->disk === 'public'));
        $photos->each(fn ($p) => Storage::disk('public')->assertExists($p->image_path));
    }

    public function test_photos_page_lists_photos_with_thumbnail_and_full_urls(): void
    {
        $shop = $this->createShop();
        [$first] = $this->addPhotos($shop, 2);

        $this->actingAs($shop->user)->get('/shop/photos')->assertInertia(fn (Assert $page) => $page
            ->component('Shop/Photos')
            ->has('photos', 2)
            ->where('photos.0.image_id', $first->image_id)
            ->has('photos.0.thumb_url')
            ->has('photos.0.url')
            ->where('limits.max_photos', ShopPhotoController::MAX_PHOTOS)
            ->where('storage', 'local')
        );
    }

    public function test_upload_rejects_non_images_and_respects_the_photo_limit(): void
    {
        $shop = $this->createShop();

        $this->actingAs($shop->user)
            ->post('/shop/photos', ['photos' => [UploadedFile::fake()->createWithContent('notes.txt', 'hello')]])
            ->assertSessionHasErrors('photos.0');

        $this->addPhotos($shop, ShopPhotoController::MAX_PHOTOS);

        $this->actingAs($shop->user)
            ->post('/shop/photos', ['photos' => [$this->png()]])
            ->assertSessionHasErrors('photos');

        $this->assertSame(ShopPhotoController::MAX_PHOTOS, $shop->galleryImages()->count());
    }

    public function test_reorder_sets_cover_and_ignores_other_shops_photos(): void
    {
        $shop = $this->createShop();
        [$a, $b, $c] = $this->addPhotos($shop, 3);

        $this->actingAs($shop->user)
            ->post('/shop/photos/reorder', ['order' => [$c->image_id, $a->image_id, $b->image_id]])
            ->assertSessionHasNoErrors();

        $this->assertSame([$c->image_id, $a->image_id, $b->image_id], $shop->galleryImages()->pluck('image_id')->all());
        $this->assertSame($c->image_id, $shop->coverImage()->first()->image_id);

        $otherShop = $this->createShop();
        [$foreign] = $this->addPhotos($otherShop, 1);

        $this->actingAs($shop->user)
            ->post('/shop/photos/reorder', ['order' => [$foreign->image_id]])
            ->assertSessionHasErrors('order.0');
    }

    public function test_delete_removes_file_and_row_but_not_other_shops_photos(): void
    {
        $shop = $this->createShop();
        $this->actingAs($shop->user)->post('/shop/photos', ['photos' => [$this->png()]]);
        $photo = $shop->galleryImages()->first();

        $this->actingAs($shop->user)->delete("/shop/images/{$photo->image_id}")->assertSessionHas('success', 'Photo deleted.');
        $this->assertModelMissing($photo);
        Storage::disk('public')->assertMissing($photo->image_path);

        $otherShop = $this->createShop();
        [$foreign] = $this->addPhotos($otherShop, 1);
        $this->actingAs($shop->user)->delete("/shop/images/{$foreign->image_id}")->assertForbidden();
        $this->assertModelExists($foreign);
    }

    public function test_new_logo_replaces_the_old_one(): void
    {
        $shop = $this->createShop();
        $profile = [
            'shop_name' => $shop->user->name,
            'email' => $shop->user->email,
            'phone' => '01800000000',
            'shop_address' => 'Dhaka',
            'device_types' => ['phone', 'laptop'],
        ];

        $this->actingAs($shop->user)->post('/shop/edit', $profile + ['logo' => $this->png('logo1.png')]);
        $first = $shop->logoImage()->first();

        $this->actingAs($shop->user)->post('/shop/edit', $profile + ['logo' => $this->png('logo2.png')]);

        $this->assertSame(1, $shop->images()->where('type', 'logo')->count());
        $this->assertModelMissing($first);
        Storage::disk('public')->assertMissing($first->image_path);
    }

    public function test_customer_shop_list_uses_first_photo_as_cover(): void
    {
        $customer = $this->createCustomer();
        $shop = $this->createShop();
        [$a, $b] = $this->addPhotos($shop, 2);
        $b->update(['sort_order' => 0]);

        $this->actingAs($customer->user)->get('/dashboard/customer')->assertInertia(fn (Assert $page) => $page
            ->where('shops.0.cover_url', route('shop.images.show', ['shopImage' => $b->image_id]))
        );
    }

    public function test_uploads_go_to_cloudinary_when_it_is_the_upload_disk(): void
    {
        config(['filesystems.shop_images_disk' => 'cloudinary', 'services.cloudinary.folder' => 'repairx']);
        $shop = $this->createShop();

        $gateway = Mockery::mock(CloudinaryGateway::class);
        $gateway->shouldReceive('upload')->once()
            ->with(Mockery::type('string'), "repairx/shops/{$shop->shop_id}/gallery")
            ->andReturn(['public_id' => 'repairx/shops/1/gallery/abc123', 'width' => 1, 'height' => 1]);
        $gateway->shouldReceive('url')->andReturnUsing(fn ($id, $t) => "https://res.cloudinary.com/demo/image/upload/{$t}/{$id}");
        $gateway->shouldReceive('destroy')->once()->with('repairx/shops/1/gallery/abc123');
        $this->app->instance(CloudinaryGateway::class, $gateway);

        $this->actingAs($shop->user)->post('/shop/photos', ['photos' => [$this->png()]])->assertSessionHas('success');

        $photo = $shop->galleryImages()->firstOrFail();
        $this->assertSame('cloudinary', $photo->disk);
        $this->assertSame('repairx/shops/1/gallery/abc123', $photo->image_path);
        Storage::disk('public')->assertDirectoryEmpty('/');

        $this->actingAs($shop->user)->get('/shop/photos')->assertInertia(fn (Assert $page) => $page
            ->where('photos.0.thumb_url', fn ($url) => str_starts_with($url, 'https://res.cloudinary.com/demo/image/upload/c_fill')
                && str_ends_with($url, 'repairx/shops/1/gallery/abc123'))
            ->where('storage', 'cloudinary')
        );

        $this->actingAs($shop->user)->delete("/shop/images/{$photo->image_id}")->assertSessionHas('success');
        $this->assertModelMissing($photo);
    }

    public function test_gateway_builds_optimised_cdn_urls_offline(): void
    {
        $gateway = new CloudinaryGateway('cloudinary://key:secret@democloud');

        $this->assertSame(
            'https://res.cloudinary.com/democloud/image/upload/c_fill,w_10/f_auto,q_auto/v1/repairx/a',
            $gateway->url('repairx/a', 'c_fill,w_10/f_auto,q_auto')
        );
        $this->assertFalse((new CloudinaryGateway(''))->isConfigured());
    }
}
