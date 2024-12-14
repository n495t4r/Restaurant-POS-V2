<?php

namespace App\Providers;

use App\Filament\Pages\OrderManagement;
use App\Repositories\ChowDeckMenuItemRepository;
use App\Services\ChowdeckService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(ChowDeckMenuItemRepository::class, function ($app) {
            return new ChowDeckMenuItemRepository($app->make(ChowdeckService::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::component('order-management', OrderManagement::class);

        // Optional: Explicit event listener registration
        Livewire::listen('order-created', function ($orderId) {
            \Log::error('Explicit Livewire Global Listener', [
                'orderId' => $orderId,
                'timestamp' => now(),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)
            ]);
        });
    }
}
