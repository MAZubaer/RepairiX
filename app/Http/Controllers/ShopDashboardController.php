<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Inertia\Inertia;

class ShopDashboardController extends Controller
{
    public function show()
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
        $gallery = $shop->galleryImages()->orderByDesc('image_id')->get(['image_id', 'image_path']);

        return Inertia::render('Dashboard/Shop', [
            'shop' => [
                'name' => $user->name,
                'motto' => $shop->motto,
                'address' => $user->location,
                'rating' => (float) $shop->rating,
                'services_provided' => $shop->services_provided,
            ],
            'logo' => $logo ? [
                'image_id' => $logo->image_id,
                'url' => route('shop.images.show', ['shopImage' => $logo->image_id]),
            ] : null,
            'gallery' => $gallery->map(function ($img) {
                return [
                    'image_id' => $img->image_id,
                    'url' => route('shop.images.show', ['shopImage' => $img->image_id]),
                ];
            }),
        ]);
    }
}
