<?php

namespace App\Http\Controllers;

use App\Models\ServiceRecord;
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

        $logo = $shop->logoImage()->first();
        $gallery = $shop->galleryImages()->get();

        $statusCounts = ServiceRecord::query()
            ->where('shop_id', $shop->shop_id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (array $statuses) => (int) collect($statuses)->sum(fn ($s) => $statusCounts[$s] ?? 0);

        $recent = ServiceRecord::query()
            ->where('shop_id', $shop->shop_id)
            ->with('customer.user:id,name')
            ->latest('service_id')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard/Shop', [
            'stats' => [
                'pending' => $count(['pending']),
                'active' => $count(['accepted', 'in progress', 'completed']),
                'finished' => $count(['sent from shop', 'delivered']),
                'earned' => (float) ServiceRecord::query()
                    ->where('shop_id', $shop->shop_id)
                    ->whereIn('status', ['completed', 'sent from shop', 'delivered'])
                    ->sum('repair_cost'),
            ],
            'subscription' => [
                'plan' => $shop->subscription_plan,
                'expiry_date' => $shop->expiry_date?->toIso8601String(),
            ],
            'recentRequests' => $recent->map(fn (ServiceRecord $r) => [
                'service_id' => $r->service_id,
                'customer_name' => $r->customer?->user?->name ?? 'Customer',
                'device_type' => $r->device_type,
                'phone_model' => $r->phone_model,
                'status' => $r->status,
                'date' => $r->created_at?->timezone(config('app.timezone'))->format('Y-m-d'),
            ])->values(),
            'shop' => [
                'name' => $user->name,
                'motto' => $shop->motto,
                'address' => $user->location,
                'rating' => (float) $shop->rating,
                'services_provided' => $shop->services_provided,
                'device_types' => $shop->deviceTypes(),
            ],
            'logo' => $logo ? [
                'image_id' => $logo->image_id,
                'url' => $logo->url('logo'),
            ] : null,
            'gallery' => $gallery->map->toGalleryArray()->values(),
        ]);
    }
}
