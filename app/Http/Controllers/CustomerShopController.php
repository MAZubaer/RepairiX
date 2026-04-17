<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRecordRequest;
use App\Models\Customer;
use App\Models\ServiceRecord;
use App\Models\Shop;
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

        $shop->load([
            'user:id,name,location',
            'logoImage:image_id,shop_id,image_path',
            'galleryImages:image_id,shop_id,image_path',
        ]);

        $gallery = $shop->galleryImages->sortByDesc('image_id')->values();

        return Inertia::render('Customer/ShopDetails', [
            'shop' => [
                'shop_id' => $shop->shop_id,
                'name' => $shop->user?->name ?? 'Shop Name',
                'motto' => $shop->motto,
                'address' => $shop->user?->location ?? 'Shop Address',
                'rating' => (float) ($shop->rating ?? 0),
                'services_provided' => $shop->services_provided,
            ],
            'logo' => $shop->logoImage ? [
                'image_id' => $shop->logoImage->image_id,
                'url' => route('shop.images.show', ['shopImage' => $shop->logoImage->image_id]),
            ] : null,
            'gallery' => $gallery->map(function ($img) {
                return [
                    'image_id' => $img->image_id,
                    'url' => route('shop.images.show', ['shopImage' => $img->image_id]),
                ];
            }),
            'requestSent' => (bool) $request->session()->get('request_sent', false),
        ]);
    }

    public function storeRequest(StoreServiceRecordRequest $request, Shop $shop)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if (! $customer) {
            return back()->withErrors([
                'server' => 'Customer profile not found. Please complete registration first.',
            ]);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $customer, $shop) {
            ServiceRecord::create([
                'customer_id' => $customer->customer_id,
                'shop_id' => $shop->shop_id,
                'phone_number' => $validated['phone_number'],
                'phone_model' => $validated['phone_model'],
                'phone_imei_number' => $validated['phone_imei_number'],
                'customer_problem' => $validated['customer_problem'],
                'shop_problem' => null,
                'status' => 'pending',
                'repair_cost' => null,
            ]);
        });

        return redirect()
            ->route('customer.shops.show', ['shop' => $shop->shop_id])
            ->with('request_sent', true);
    }
}
