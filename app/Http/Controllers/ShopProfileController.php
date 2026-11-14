<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopProfileRequest;
use App\Models\Shop;
use App\Models\ShopImage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ShopProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $shop = Shop::firstOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_status' => 'not_activated',
                'expiry_date' => null,
                'description' => null,
                'rating' => 0,
            ]
        );

        $logo = $shop->logoImage()->latest('image_id')->first();
        $gallery = $shop->galleryImages()->orderByDesc('image_id')->get(['image_id', 'image_path', 'type']);

        return Inertia::render('Shop/EditProfile', [
            'profile' => [
                'shop_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'shop_address' => $user->location,
                'motto' => $shop->motto,
                'services_provided' => $shop->services_provided,
            ],
            'logo' => $logo ? [
                'image_id' => $logo->image_id,
                'image_path' => $logo->image_path,
                'url' => route('shop.images.show', ['shopImage' => $logo->image_id]),
            ] : null,
            'gallery' => $gallery->map(function ($img) {
                return [
                    'image_id' => $img->image_id,
                    'image_path' => $img->image_path,
                    'url' => route('shop.images.show', ['shopImage' => $img->image_id]),
                ];
            }),
        ]);
    }

    public function showImage(Request $request, ShopImage $shopImage)
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['shop', 'customer'], true)) {
            abort(403);
        }

        if ($user->role === 'shop') {
            $shop = $shopImage->shop;
            if (! $shop || (int) $shop->user_id !== (int) $user->id) {
                abort(403);
            }
        }

        $disk = $this->imageDisk();

        if (! $shopImage->image_path || ! $disk->exists($shopImage->image_path)) {
            abort(404);
        }

        // response() streams from any disk (local or S3), unlike response()->file().
        return $disk->response($shopImage->image_path);
    }

    public function destroyImage(Request $request, ShopImage $shopImage)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $shop = $shopImage->shop;
        if (! $shop || (int) $shop->user_id !== (int) $user->id) {
            abort(403);
        }

        DB::transaction(function () use ($shopImage) {
            if ($shopImage->image_path) {
                $this->imageDisk()->delete($shopImage->image_path);
            }

            $shopImage->delete();
        });

        return redirect()->route('shop.edit')->with('success', 'Image deleted successfully.');
    }

    public function update(UpdateShopProfileRequest $request)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated, $request) {
            $shop = Shop::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'subscription_status' => 'not_activated',
                    'expiry_date' => null,
                    'description' => null,
                    'rating' => 0,
                ]
            );

            $user->update([
                'name' => $validated['shop_name'],
                'phone' => $validated['phone'],
                'location' => $validated['shop_address'],
            ]);

            $shop->update([
                'motto' => $validated['motto'] ?? null,
                'services_provided' => $validated['services_provided'] ?? null,
                // Keep previous description behavior aligned with address.
                'description' => $validated['shop_address'],
            ]);

            if ($request->hasFile('logo')) {
                $oldLogoPaths = ShopImage::query()
                    ->where('shop_id', $shop->shop_id)
                    ->where('type', 'logo')
                    ->pluck('image_path');

                foreach ($oldLogoPaths as $oldLogoPath) {
                    if ($oldLogoPath) {
                        $this->imageDisk()->delete($oldLogoPath);
                    }
                }

                ShopImage::query()
                    ->where('shop_id', $shop->shop_id)
                    ->where('type', 'logo')
                    ->delete();

                $logoPath = $request->file('logo')->store('shop_images/logos', $this->imageDiskName());
                ShopImage::create([
                    'shop_id' => $shop->shop_id,
                    'image_path' => $logoPath,
                    'type' => 'logo',
                ]);
            }

            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $galleryImage) {
                    $galleryPath = $galleryImage->store('shop_images/gallery', $this->imageDiskName());
                    ShopImage::create([
                        'shop_id' => $shop->shop_id,
                        'image_path' => $galleryPath,
                        'type' => 'gallery',
                    ]);
                }
            }
        });

        return redirect()->route('shop.edit')->with('success', 'Profile updated successfully.');
    }

    private function imageDiskName(): string
    {
        return (string) config('filesystems.shop_images_disk', 'public');
    }

    private function imageDisk(): FilesystemAdapter
    {
        return Storage::disk($this->imageDiskName());
    }
}
