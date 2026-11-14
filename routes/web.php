<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Auth\CustomerRegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ShopRegisterController;
use App\Http\Controllers\ShopProfileController;
use App\Http\Controllers\ShopDashboardController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\CustomerShopController;
use App\Http\Controllers\ShopCustomerManagementController;
use App\Http\Controllers\CustomerUpdatesController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\ShopBillingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ShopManagementController as AdminShopManagementController;
use App\Http\Controllers\Admin\ServiceRequestController as AdminServiceRequestController;
use App\Http\Controllers\Admin\CustomerManagementController as AdminCustomerManagementController;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/about', function () {
    $user = Auth::user();

    return Inertia::render('About', [
        'role' => $user?->role,
    ]);
})->name('about');

// Guest-only pages: registration and login
Route::middleware('guest')->group(function () {
    Route::get('/register/customer', [CustomerRegisterController::class, 'create'])->name('register.customer');
    Route::post('/register/customer', [CustomerRegisterController::class, 'store'])->name('register.customer.store');

    Route::get('/register/shop', [ShopRegisterController::class, 'create'])->name('register.shop');
    Route::post('/register/shop', [ShopRegisterController::class, 'store'])->name('register.shop.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Dashboards
Route::get('/dashboard/customer', [CustomerDashboardController::class, 'show'])
    ->name('dashboard.customer')
    ->middleware('auth');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin.role'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/shops', [AdminShopManagementController::class, 'index'])->name('shops.index');
        Route::post('/shops/{shop}/toggle-status', [AdminShopManagementController::class, 'toggleStatus'])->name('shops.toggleStatus');
        Route::post('/shops/{shop}/plan', [AdminShopManagementController::class, 'updatePlan'])->name('shops.updatePlan');
        Route::post('/shops/{shop}/expiry', [AdminShopManagementController::class, 'updateExpiry'])->name('shops.updateExpiry');
        Route::get('/service-requests', [AdminServiceRequestController::class, 'index'])->name('serviceRequests.index');
        Route::get('/customers', [AdminCustomerManagementController::class, 'index'])->name('customers.index');
        Route::delete('/customers/{customer}', [AdminCustomerManagementController::class, 'destroy'])->name('customers.destroy');
    });

Route::get('/customer/shops/{shop}', [CustomerShopController::class, 'show'])
    ->name('customer.shops.show')
    ->middleware('auth');

Route::post('/customer/shops/{shop}/requests', [CustomerShopController::class, 'storeRequest'])
    ->name('customer.shops.requests.store')
    ->middleware('auth');

Route::get('/update', [CustomerUpdatesController::class, 'show'])
    ->name('customer.updates')
    ->middleware('auth');

Route::get('/history', [CustomerUpdatesController::class, 'showHistory'])
    ->name('customer.history')
    ->middleware('auth');

Route::get('/notifications', [CustomerNotificationController::class, 'show'])
    ->name('customer.notifications')
    ->middleware('auth');

Route::post('/customer/updates/{serviceRecord}/accept', [CustomerUpdatesController::class, 'acceptDelivery'])
    ->name('customer.updates.accept')
    ->middleware('auth');

Route::post('/customer/updates/{serviceRecord}/rating', [CustomerUpdatesController::class, 'submitRating'])
    ->name('customer.updates.rating')
    ->middleware('auth');

Route::get('/dashboard/shop', [ShopDashboardController::class, 'show'])
    ->name('dashboard.shop')
    ->middleware(['auth', 'shop.subscription']);

Route::get('/shop/pricing', [ShopBillingController::class, 'pricing'])
    ->name('shop.pricing')
    ->middleware('auth');

Route::post('/shop/billing/checkout', [ShopBillingController::class, 'createCheckoutSession'])
    ->name('shop.billing.checkout')
    ->middleware('auth');

Route::get('/shop/billing/success', [ShopBillingController::class, 'success'])
    ->name('shop.billing.success')
    ->middleware('auth');

Route::post('/stripe/webhook', [ShopBillingController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

Route::get('/stripe/webhook', function () {
    return response()->json([
        'message' => 'Stripe webhook endpoint is active. Send POST requests from Stripe.',
    ]);
});

Route::get('/shop/customers', [ShopCustomerManagementController::class, 'show'])
    ->name('shop.customers.index')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/customers/{serviceRecord}/problem', [ShopCustomerManagementController::class, 'updateProblem'])
    ->name('shop.customers.problem.update')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/customers/{serviceRecord}/cost', [ShopCustomerManagementController::class, 'updateCost'])
    ->name('shop.customers.cost.update')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/customers/{serviceRecord}/status', [ShopCustomerManagementController::class, 'updateStatus'])
    ->name('shop.customers.status.update')
    ->middleware(['auth', 'shop.subscription']);

Route::get('/shop/edit', [ShopProfileController::class, 'edit'])
    ->name('shop.edit')
    ->middleware(['auth', 'shop.subscription']);

Route::post('/shop/edit', [ShopProfileController::class, 'update'])
    ->name('shop.update')
    ->middleware(['auth', 'shop.subscription']);

Route::get('/shop/images/{shopImage}', [ShopProfileController::class, 'showImage'])
    ->name('shop.images.show')
    ->middleware('auth');

Route::delete('/shop/images/{shopImage}', [ShopProfileController::class, 'destroyImage'])
    ->name('shop.images.destroy')
    ->middleware(['auth', 'shop.subscription']);
