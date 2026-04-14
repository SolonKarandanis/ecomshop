<?php

namespace App\Providers;

use App\Repositories\AddressRepository;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ProductRepository;
use App\Repositories\StripeOrderDetailRepository;
use App\Repositories\UserRepository;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\StripeService;
use App\Services\UserService;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService($app->make(CartRepository::class), $app->make(ProductRepository::class));
        });

        $this->app->singleton(UserService::class, function ($app) {
            return new UserService($app->make(UserRepository::class));
        });

        $this->app->singleton(StripeService::class, function ($app) {
            return new StripeService();
        });

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(OrderRepository::class),
                $app->make(AddressRepository::class),
                $app->make(PaymentMethodRepository::class),
                $app->make(StripeOrderDetailRepository::class),
                $app->make(CartService::class),
                $app->make(StripeService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Paginator::useTailwind();

        if (! $this->app->runningInConsole()) {
            FilamentAsset::register([
                // Local asset build using Vite
                Js::make('sweetalert2', Vite::asset('resources/js/app.js')),
            ]);
        }
    }
}
