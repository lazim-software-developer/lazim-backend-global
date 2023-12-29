<?php

namespace App\Providers;

use App\Models\User\User;
use Illuminate\View\View;
use App\Models\Forms\Guest;
use App\Models\Forms\SaleNOC;
use App\Models\Accounting\WDA;
use App\Models\Community\Post;
use App\Observers\WDAObserver;
use App\Models\Forms\MoveInOut;
use App\Models\ResidentialForm;
use App\Models\Vendor\Contract;
use App\Observers\UserObserver;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\OwnerAssociation;
use App\Models\TechnicianAssets;
use App\Observers\GuestObserver;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Community\Comment;
use App\Observers\TenderObserver;
use App\Models\Accounting\Invoice;
use App\Models\Building\Complaint;
use App\Models\Community\PostLike;
use App\Observers\CommentObserver;
use App\Observers\InvoiceObserver;
use App\Observers\SaleNOCObserver;
use App\Models\Accounting\Proposal;
use App\Observers\BuildingObserver;
use App\Observers\ContractObserver;
use App\Observers\DocumentObserver;
use App\Observers\PostLikeObserver;
use App\Observers\ProposalObserver;
use App\Observers\ComplaintObserver;
use App\Observers\MoveInOutObserver;
use App\Observers\AccessCardObserver;
use App\Observers\FitOutFormObserver;
use Illuminate\Support\Facades\Schema;
use App\Observers\AnnouncementObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\Building\FacilityBooking;
use App\Observers\ResidentialFormObserver;
use Filament\Support\Facades\FilamentView;
use App\Observers\OwnerAssociationObserver;
use App\Observers\TechnicianAssetsObserver;
use App\Observers\FacilityServiceBookingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        FilamentView::registerRenderHook(
            'panels::footer',
            fn (): View => view('filament.hooks.footer'),
        );
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
        Comment::observe(CommentObserver::class);
        FacilityBooking::observe(FacilityServiceBookingObserver::class);
        PostLike::observe(PostLikeObserver::class);
        User::observe(UserObserver::class);
        Proposal::observe(ProposalObserver::class);
        WDA::observe(WDAObserver::class);
        Contract::observe(ContractObserver::class);
        Document::observe(DocumentObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Tender::observe(TenderObserver::class);
        TechnicianAssets::observe(TechnicianAssetsObserver::class);
    }
}
