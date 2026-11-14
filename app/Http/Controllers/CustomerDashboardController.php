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
                'logoImage',
                'coverImage',
            ])
            ->whereHas('user', function ($q) {
                $q->where('role', 'shop');
            })
            ->where('subscription_status', 'activated')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->orderByDesc('shop_id')
            ->get(['shop_id', 'user_id', 'rating', 'device_types'])
            ->map(function ($shop) {
                return [
                    'shop_id' => $shop->shop_id,
                    'name' => $shop->user?->name ?? 'Shop',
                    'location' => $shop->user?->location ?? 'Location not set',
                    'rating' => (float) ($shop->rating ?? 0),
                    'device_types' => $shop->deviceTypes(),
                    'logo_url' => $shop->logoImage?->url('logo'),
                    // The shop's first photo, shown as its card cover.
                    'cover_url' => $shop->coverImage?->url('cover'),
                ];
            });

        return Inertia::render('Dashboard/Customer', [
            'customerName' => $user->name,
            'shops' => $shops,
        ]);
    }
}
