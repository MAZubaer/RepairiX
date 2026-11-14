<?php

namespace App\Providers;

use App\Services\Images\CloudinaryGateway;
use App\Services\Images\ShopImageStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CloudinaryGateway::class, fn () => new CloudinaryGateway(config('services.cloudinary.url')));
        $this->app->singleton(ShopImageStorage::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
