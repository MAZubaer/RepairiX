<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ServiceRecord;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerUpdatesController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('Customer/Updates', [
            'records' => $this->mappedRecordsForCustomer($request),
        ]);
    }

    public function showHistory(Request $request)
    {
        return Inertia::render('Customer/History', [
            'records' => $this->mappedRecordsForCustomer($request, ['completed', 'delivered']),
        ]);
    }

    public function acceptDelivery(Request $request, ServiceRecord $serviceRecord)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if (! $customer || (int) $serviceRecord->customer_id !== (int) $customer->customer_id) {
            abort(403);
        }

        if ($serviceRecord->status !== 'sent from shop') {
            return back()->withErrors([
                'status' => 'Only requests marked as sent from shop can be accepted.',
            ]);
        }

        $serviceRecord->status = 'delivered';
        $serviceRecord->save();

        return back();
    }

    public function submitRating(Request $request, ServiceRecord $serviceRecord)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if (! $customer || (int) $serviceRecord->customer_id !== (int) $customer->customer_id) {
            abort(403);
        }

        if ($serviceRecord->status !== 'delivered') {
            return back()->withErrors([
                'rating' => 'You can rate only after accepting delivery.',
            ]);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
        ]);

        $serviceRecord->rating = (int) $validated['rating'];
        $serviceRecord->save();

        $averageRating = ServiceRecord::query()
            ->where('shop_id', $serviceRecord->shop_id)
            ->where('status', 'delivered')
            ->whereNotNull('rating')
            ->avg('rating');

        Shop::where('shop_id', $serviceRecord->shop_id)->update([
            'rating' => $averageRating ? round((float) $averageRating, 2) : 0,
        ]);

        return back();
    }

    private function mappedRecordsForCustomer(Request $request, ?array $statusFilter = null)
    {
        $user = $request->user();
        if (! $user || $user->role !== 'customer') {
            abort(403);
        }

        $customer = Customer::where('user_id', $user->id)->first();
        if (! $customer) {
            return collect();
        }

        $query = ServiceRecord::query()
            ->where('customer_id', $customer->customer_id)
            ->with([
                'shop:shop_id,user_id',
                'shop.user:id,name',
            ])
            ->orderByDesc('service_id');

        if ($statusFilter && count($statusFilter)) {
            $query->whereIn('status', $statusFilter);
        }

        return $query->get()->map(function (ServiceRecord $record) {
            return [
                'service_id' => $record->service_id,
                'customer_phone' => $record->phone_number,
                'phone_model' => $record->phone_model,
                'contact' => $record->phone_number,
                'phone_imei_number' => $record->phone_imei_number,
                'customer_problem' => $record->customer_problem,
                'shop_problem' => $record->shop_problem,
                'repair_cost' => $record->repair_cost,
                'rating' => $record->rating,
                'status' => $record->status,
                'shop_name' => $record->shop?->user?->name ?? 'Shop',
                'date' => $record->created_at?->timezone(config('app.timezone'))->format('Y-m-d'),
            ];
        })->values();
    }
}
