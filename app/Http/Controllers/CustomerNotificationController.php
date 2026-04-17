<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerNotificationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if (! $customer) {
            return Inertia::render('Customer/Notifications', [
                'notifications' => [],
            ]);
        }

        $notifications = CustomerNotification::query()
            ->where('customer_id', $customer->customer_id)
            ->latest('id')
            ->get()
            ->map(function (CustomerNotification $notification) {
                $timestamp = $notification->created_at?->timezone(config('app.timezone'));

                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'time' => $timestamp?->format('Y-m-d h:i A'),
                    'relative_time' => $timestamp?->diffForHumans(),
                ];
            })
            ->values();

        return Inertia::render('Customer/Notifications', [
            'notifications' => $notifications,
        ]);
    }
}
