<?php

namespace App\Providers;

use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use App\Observers\BuildingObserver;
use App\Observers\OwnerAssociationObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // TODO: NEED TO CHECK IF THIS WORKS AND TO BE REMOVED LATER
        // JsonResource::withoutWrapping();

        // Observers
        OwnerAssociation::observe(OwnerAssociationObserver::class);
        Building::observe(BuildingObserver::class);
    }
}
