<?php

namespace App\Services\Images;

use App\Models\Shop;
use App\Models\ShopImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Stores, deletes and builds URLs for shop logos and photos.
 *
 * New uploads go to the configured disk (Cloudinary or a Laravel disk). Every image row
 * remembers its own disk, so URLs and deletes keep working for images uploaded before a switch.
 */
class ShopImageStorage
{
    public const CLOUDINARY = 'cloudinary';

    /** Cloudinary transformations per use. f_auto/q_auto serve WebP/AVIF at a sensible quality. */
    private const VARIANTS = [
        'thumb' => 'c_fill,g_auto,w_640,h_480/f_auto,q_auto',
        'full' => 'c_limit,w_1600,h_1600/f_auto,q_auto',
        'logo' => 'c_fill,g_auto,w_256,h_256/f_auto,q_auto',
        'cover' => 'c_fill,g_auto,w_800,h_400/f_auto,q_auto',
    ];

    public function __construct(private readonly CloudinaryGateway $cloudinary)
    {
    }

    /** The disk new uploads go to. */
    public function uploadDisk(): string
    {
        return (string) config('filesystems.shop_images_disk', 'public');
    }

    public function store(Shop $shop, UploadedFile $file, string $type): ShopImage
    {
        $disk = $this->uploadDisk();
        $folder = $type === 'logo' ? 'logos' : 'gallery';

        if ($disk === self::CLOUDINARY) {
            $root = trim((string) config('services.cloudinary.folder', 'Repairix'), '/');
            $uploaded = $this->cloudinary->upload($file->getRealPath(), "{$root}/shops/{$shop->shop_id}/{$folder}");
            $path = $uploaded['public_id'];
        } else {
            $path = $file->store("shop_images/{$folder}", $disk);
        }

        return ShopImage::create([
            'shop_id' => $shop->shop_id,
            'image_path' => $path,
            'disk' => $disk,
            'type' => $type,
            'sort_order' => $type === 'gallery' ? $this->nextSortOrder($shop) : 0,
        ]);
    }

    /** Delete the stored file, then the row. A missing remote file never blocks the delete. */
    public function delete(ShopImage $image): void
    {
        try {
            if ($image->disk === self::CLOUDINARY) {
                $this->cloudinary->destroy($image->image_path);
            } elseif ($image->image_path) {
                Storage::disk($image->disk ?: 'public')->delete($image->image_path);
            }
        } catch (Throwable $e) {
            Log::warning('Could not delete stored shop image file.', [
                'image_id' => $image->image_id,
                'disk' => $image->disk,
                'error' => $e->getMessage(),
            ]);
        }

        $image->delete();
    }

    public function url(ShopImage $image, string $variant = 'full'): string
    {
        if ($image->disk === self::CLOUDINARY) {
            return $this->cloudinary->url($image->image_path, self::VARIANTS[$variant] ?? self::VARIANTS['full']);
        }

        // Local files are streamed through the app (the route checks the viewer's role).
        return route('shop.images.show', ['shopImage' => $image->image_id]);
    }

    private function nextSortOrder(Shop $shop): int
    {
        return (int) ShopImage::where('shop_id', $shop->shop_id)->where('type', 'gallery')->max('sort_order') + 1;
    }
}
