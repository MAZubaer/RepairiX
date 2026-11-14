<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRecordRequest;
use App\Models\Customer;
use App\Models\ServiceRecord;
use App\Models\Shop;
use App\Support\DeviceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerShopController extends Controller
{
    public function show(Request $request, Shop $shop)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $isShopActive = $shop->subscription_status === 'activated'
            && $shop->expiry_date !== null
            && $shop->expiry_date->isFuture();

        if (! $isShopActive) {
            abort(404);
        }

        $shop->load([
            'user:id,name,location',
            'logoImage',
            'galleryImages',
        ]);

        return Inertia::render('Customer/ShopDetails', [
            'shop' => [
                'shop_id' => $shop->shop_id,
                'name' => $shop->user?->name ?? 'Shop Name',
                'motto' => $shop->motto,
                'address' => $shop->user?->location ?? 'Shop Address',
                'rating' => (float) ($shop->rating ?? 0),
                'services_provided' => $shop->services_provided,
                'device_types' => $shop->deviceTypes(),
            ],
            'logo' => $shop->logoImage ? [
                'image_id' => $shop->logoImage->image_id,
                'url' => $shop->logoImage->url('logo'),
            ] : null,
            'gallery' => $shop->galleryImages->map->toGalleryArray()->values(),
        ]);
    }

    public function storeRequest(StoreServiceRecordRequest $request, Shop $shop)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $isShopActive = $shop->subscription_status === 'activated'
            && $shop->expiry_date !== null
            && $shop->expiry_date->isFuture();

        if (! $isShopActive) {
            return back()->withErrors([
                'server' => 'This shop is not active right now.',
            ]);
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if (! $customer) {
            return back()->withErrors([
                'server' => 'Customer profile not found. Please complete registration first.',
            ]);
        }

        $validated = $request->validated();

        if (! $shop->repairs($validated['device_type'])) {
            return back()->withErrors([
                'device_type' => 'This shop doesn\'t repair '.DeviceType::PLURAL_LABELS[$validated['device_type']].'.',
            ]);
        }

        DB::transaction(function () use ($validated, $customer, $shop) {
            ServiceRecord::create([
                'customer_id' => $customer->customer_id,
                'shop_id' => $shop->shop_id,
                'device_type' => $validated['device_type'],
                'phone_number' => $validated['phone_number'],
                'phone_model' => $validated['phone_model'],
                'phone_imei_number' => $validated['phone_imei_number'] ?? null,
                'customer_problem' => $validated['customer_problem'],
                'shop_problem' => null,
                'status' => 'pending',
                'repair_cost' => null,
            ]);
        });

        return redirect()
            ->route('customer.shops.show', ['shop' => $shop->shop_id])
            ->with('success', 'Request sent! You can follow it under My repairs.');
    }
}
