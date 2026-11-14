<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateShopProfileRequest;
use App\Models\Shop;
use App\Models\ShopImage;
use App\Services\Images\ShopImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ShopProfileController extends Controller
{
    public function __construct(private readonly ShopImageStorage $images)
    {
    }

    public function edit()
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $shop = $this->shopFor($user);
        $logo = $shop->logoImage()->first();

        return Inertia::render('Shop/EditProfile', [
            'profile' => [
                'shop_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'shop_address' => $user->location,
                'motto' => $shop->motto,
                'services_provided' => $shop->services_provided,
                'device_types' => $shop->deviceTypes(),
            ],
            'logo' => $logo ? [
                'image_id' => $logo->image_id,
                'url' => $logo->url('logo'),
            ] : null,
            'photoCount' => $shop->galleryImages()->count(),
        ]);
    }

    /**
     * Serve an image stored on a local disk. Cloudinary images are public CDN URLs,
     * so requests for those are simply redirected there.
     */
    public function showImage(Request $request, ShopImage $shopImage)
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['shop', 'customer', 'admin'], true)) {
            abort(403);
        }

        if ($user->role === 'shop') {
            $shop = $shopImage->shop;
            if (! $shop || (int) $shop->user_id !== (int) $user->id) {
                abort(403);
            }
        }

        if ($shopImage->disk === ShopImageStorage::CLOUDINARY) {
            return redirect()->away($shopImage->url($request->query('variant', 'full')));
        }

        $disk = Storage::disk($shopImage->disk ?: 'public');

        if (! $shopImage->image_path || ! $disk->exists($shopImage->image_path)) {
            abort(404);
        }

        return $disk->response($shopImage->image_path);
    }

    public function update(UpdateShopProfileRequest $request)
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'shop') {
            abort(403);
        }

        $validated = $request->validated();
        $shop = $this->shopFor($user);

        DB::transaction(function () use ($user, $shop, $validated) {
            $user->update([
                'name' => $validated['shop_name'],
                'phone' => $validated['phone'],
                'location' => $validated['shop_address'],
            ]);

            $shop->update([
                'motto' => $validated['motto'] ?? null,
                'services_provided' => $validated['services_provided'] ?? null,
                'device_types' => array_values(array_unique($validated['device_types'])),
                // Keep previous description behavior aligned with address.
                'description' => $validated['shop_address'],
            ]);
        });

        // Uploads happen outside the DB transaction: a remote upload can't be rolled back anyway.
        if ($request->hasFile('logo')) {
            $oldLogos = $shop->images()->where('type', 'logo')->get();
            $this->images->store($shop, $request->file('logo'), 'logo');
            $oldLogos->each(fn (ShopImage $old) => $this->images->delete($old));
        }

        return redirect()->route('shop.edit')->with('success', 'Profile updated successfully.');
    }

    private function shopFor($user): Shop
    {
        return Shop::firstOrCreate(
            ['user_id' => $user->id],
            [
                'subscription_status' => 'not_activated',
                'expiry_date' => null,
                'description' => null,
                'rating' => 0,
            ]
        );
    }
}
