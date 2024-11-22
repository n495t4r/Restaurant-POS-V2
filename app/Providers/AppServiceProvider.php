<?php

namespace App\Providers;

use App\Repositories\ChowDeckMenuItemRepository;
use App\Services\ChowdeckService;
use Illuminate\Support\ServiceProvider;

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
        //
    }
}
