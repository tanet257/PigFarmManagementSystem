<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\InventoryMovement;
use App\Models\Cost;
use App\Observers\InventoryMovementObserver;
use App\Observers\CostObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Register observers
        InventoryMovement::observe(InventoryMovementObserver::class);
        Cost::observe(CostObserver::class);
    }
}
