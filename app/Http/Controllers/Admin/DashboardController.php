<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRecord;
use App\Models\Shop;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDealers = Shop::count();
        $activeSubscriptions = Shop::where('subscription_status', 'activated')->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalRepairsCompleted = ServiceRecord::whereIn('status', ['completed', 'sent from shop', 'delivered'])->count();

        // Sum of repair costs on finished jobs (money paid to shops), shown as "Total Repair Value".
        // Subscription income lives in Stripe; there is no payments table to sum it from.
        $totalRevenue = ServiceRecord::whereIn('status', ['completed', 'sent from shop', 'delivered'])
            ->whereNotNull('repair_cost')
            ->sum('repair_cost');

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalDealers' => $totalDealers,
                'activeSubscriptions' => $activeSubscriptions,
                'totalCustomers' => $totalCustomers,
                'totalRepairsCompleted' => $totalRepairsCompleted,
                'totalRevenue' => (float) $totalRevenue,
            ],
        ]);
    }
}
