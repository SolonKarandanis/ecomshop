<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Payments\PaymentHandlerFactory;
use App\Payments\StripePaymentHandler;
use App\Repositories\AddressRepository;
use App\Repositories\CartRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ProductRepository;
use App\Repositories\RoleRepository;
use App\Repositories\StripeOrderDetailRepository;
use App\Repositories\UserRepository;
use App\Services\CartService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\StripeService;
use App\Services\UserService;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
            return new UserService(
                $app->make(UserRepository::class),
                $app->make(RoleRepository::class),
            );
        });

        $this->app->singleton(StripeService::class, function ($app) {
            return new StripeService();
        });

        $this->app->singleton(StripePaymentHandler::class, function ($app) {
            return new StripePaymentHandler($app->make(StripeService::class), $app->make(StripeOrderDetailRepository::class));
        });

        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService(
                $app->make(OrderRepository::class),
                $app->make(AddressRepository::class),
                $app->make(PaymentMethodRepository::class),
                $app->make(CartService::class),
                $app->make(StripeService::class),
                $app->make(PaymentHandlerFactory::class),
                $app->make(NotificationService::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);

        Gate::define('buyer-action', function (?User $user) {
            return $user === null || $user->isBuyer();
        });

        Schema::defaultStringLength(191);

        Paginator::useTailwind();

        if (config('database.default') === 'sqlite' &&
            file_exists(config('database.connections.sqlite.database'))) {
            DB::statement('PRAGMA journal_mode=WAL');
            DB::statement('PRAGMA synchronous=NORMAL');
            DB::statement('PRAGMA cache_size=10000');
            DB::statement('PRAGMA temp_store=MEMORY');
        }

        if (! $this->app->runningInConsole()) {
            FilamentAsset::register([
                // Local asset build using Vite
                Js::make('sweetalert2', Vite::asset('resources/js/app.js')),
            ]);
        }
    }
}
