<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\ShopImage;
use App\Services\Images\ShopImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Throwable;

class ShopPhotoController extends Controller
{
    /** Most photos a shop can have. Keeps pages fast and storage predictable. */
    public const MAX_PHOTOS = 20;

    /** Per-file limit in kilobytes (Cloudinary's free plan allows up to 10 MB). */
    public const MAX_FILE_KB = 5120;

    public function __construct(private readonly ShopImageStorage $images)
    {
    }

    public function index(Request $request)
    {
        $shop = $this->shopFor($request);

        return Inertia::render('Shop/Photos', [
            'photos' => $shop->galleryImages()->get()->map->toGalleryArray()->values(),
            'limits' => [
                'max_photos' => self::MAX_PHOTOS,
                'max_file_mb' => self::MAX_FILE_KB / 1024,
            ],
            'storage' => $this->images->uploadDisk() === ShopImageStorage::CLOUDINARY ? 'cloudinary' : 'local',
        ]);
    }

    public function store(Request $request)
    {
        $shop = $this->shopFor($request);
        $remaining = self::MAX_PHOTOS - $shop->galleryImages()->count();

        $request->validate([
            'photos' => ['required', 'array', 'min:1', 'max:'.max($remaining, 0)],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:'.self::MAX_FILE_KB],
        ], [
            'photos.max' => $remaining > 0
                ? "You can add {$remaining} more photo(s). The limit is ".self::MAX_PHOTOS.'.'
                : 'You have reached the limit of '.self::MAX_PHOTOS.' photos. Delete some to add new ones.',
            'photos.*.max' => 'Each photo must be '.(self::MAX_FILE_KB / 1024).' MB or smaller.',
            'photos.*.image' => 'Only image files can be uploaded.',
            'photos.*.mimes' => 'Photos must be JPG, PNG or WebP.',
        ]);

        $saved = 0;

        try {
            foreach ($request->file('photos') as $file) {
                $this->images->store($shop, $file, 'gallery');
                $saved++;
            }
        } catch (Throwable $e) {
            Log::error('Shop photo upload failed.', ['shop_id' => $shop->shop_id, 'exception' => $e]);

            $message = $saved > 0
                ? "{$saved} photo(s) uploaded, but the rest failed. Please try again."
                : 'Upload failed. Please try again in a moment.';

            return back()->withErrors(['photos' => $message]);
        }

        return back()->with('success', $saved === 1 ? 'Photo added.' : "{$saved} photos added.");
    }

    /** Save a new photo order. The first photo becomes the shop's cover. */
    public function reorder(Request $request)
    {
        $shop = $this->shopFor($request);

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => [
                'integer',
                Rule::exists('shop_images', 'image_id')->where('shop_id', $shop->shop_id)->where('type', 'gallery'),
            ],
        ]);

        DB::transaction(function () use ($validated, $shop) {
            foreach (array_values($validated['order']) as $position => $imageId) {
                ShopImage::where('image_id', $imageId)
                    ->where('shop_id', $shop->shop_id)
                    ->update(['sort_order' => $position + 1]);
            }
        });

        return back();
    }

    /** Delete a photo or the logo. */
    public function destroy(Request $request, ShopImage $shopImage)
    {
        $shop = $this->shopFor($request);

        if ((int) $shopImage->shop_id !== (int) $shop->shop_id) {
            abort(403);
        }

        $isLogo = $shopImage->type === 'logo';
        $this->images->delete($shopImage);

        return back()->with('success', $isLogo ? 'Logo removed.' : 'Photo deleted.');
    }

    private function shopFor(Request $request): Shop
    {
        $user = $request->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        return Shop::firstOrCreate(
            ['user_id' => $user->id],
            ['subscription_status' => 'not_activated', 'expiry_date' => null, 'description' => null, 'rating' => 0]
        );
    }
}
