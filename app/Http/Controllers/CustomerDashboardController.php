<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Inertia\Inertia;

class CustomerDashboardController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $shops = Shop::query()
            ->with([
                'user:id,name,location',
                'logoImage:image_id,shop_id,image_path',
            ])
            ->whereHas('user', function ($q) {
                $q->where('role', 'shop');
            })
            ->orderByDesc('shop_id')
            ->get(['shop_id', 'user_id', 'rating'])
            ->map(function ($shop) {
                return [
                    'shop_id' => $shop->shop_id,
                    'name' => $shop->user?->name ?? 'Shop',
                    'location' => $shop->user?->location ?? 'Location not set',
                    'rating' => (float) ($shop->rating ?? 0),
                    'logo_url' => $shop->logoImage
                        ? route('shop.images.show', ['shopImage' => $shop->logoImage->image_id])
                        : null,
                ];
            });

        return Inertia::render('Dashboard/Customer', [
            'customerName' => $user->name,
            'shops' => $shops,
        ]);
    }
}
