<?php

namespace App\Providers;

use Filament\Facades\Filament;
use App\Filament\Pages\CustomPage;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Filament::registerPages([
            // CustomPage::class, // Register your custom page here
        ]);
    }
}
