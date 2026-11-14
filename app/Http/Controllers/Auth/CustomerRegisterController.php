<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterCustomerRequest;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CustomerRegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('RegisterCustomer');
    }

    public function store(RegisterCustomerRequest $request)
    {
        try {
            $validated = $request->validated();

            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'location' => $validated['location'],
                    'phone' => $validated['phone'],
                    'role' => 'customer',
                    // Password is hashed by User model cast.
                    'password' => $validated['password'],
                ]);

                Customer::create([
                    'user_id' => $user->id,
                    'primary_device' => $validated['primary_device'],
                ]);

                return $user;
            });

            // Log the user in and redirect to customer dashboard
            Auth::login($user);
            return redirect()->route('dashboard.customer');
        } catch (\Throwable $e) {
            // Log the details for debugging, but never show raw exception text to the user.
            Log::error('Customer registration failed.', ['exception' => $e]);

            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['server' => 'Unable to create your account right now. Please try again.']);
        }
    }
}
