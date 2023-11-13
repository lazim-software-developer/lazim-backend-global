<?php

namespace App\Providers;

use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Community\Post;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Forms\Guest;
use App\Models\Forms\MoveInOut;
use App\Models\Forms\SaleNOC;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use App\Observers\AccessCardObserver;
use App\Observers\AnnouncementObserver;
use App\Observers\BuildingObserver;
use App\Observers\ComplaintObserver;
use App\Observers\FitOutFormObserver;
use App\Observers\GuestObserver;
use App\Observers\MoveInOutObserver;
use App\Observers\OwnerAssociationObserver;
use App\Observers\ResidentialFormObserver;
use App\Observers\SaleNOCObserver;
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
        Post::observe(AnnouncementObserver::class);
        Complaint::observe(ComplaintObserver::class);
        Guest::observe(GuestObserver::class);
        MoveInOut::observe(MoveInOutObserver::class);
        FitOutForm::observe(FitOutFormObserver::class);
        AccessCard::observe(AccessCardObserver::class);
        ResidentialForm::observe(ResidentialFormObserver::class);
        SaleNOC::observe(SaleNOCObserver::class);
    }
}
