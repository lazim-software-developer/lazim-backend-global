<?php

namespace App\Providers;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\Proposal;
use App\Models\Accounting\Tender;
use App\Models\Accounting\WDA;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\Document;
use App\Models\Building\FacilityBooking;
use App\Models\Community\Comment;
use App\Models\Community\Post;
use App\Models\Community\PostLike;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Forms\Guest;
use App\Models\Forms\MoveInOut;
use App\Models\Forms\SaleNOC;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use App\Models\TechnicianAssets;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Observers\AccessCardObserver;
use App\Observers\AnnouncementObserver;
use App\Observers\BuildingObserver;
use App\Observers\CommentObserver;
use App\Observers\ComplaintObserver;
use App\Observers\ContractObserver;
use App\Observers\DocumentObserver;
use App\Observers\FacilityServiceBookingObserver;
use App\Observers\FitOutFormObserver;
use App\Observers\GuestObserver;
use App\Observers\InvoiceObserver;
use App\Observers\MoveInOutObserver;
use App\Observers\OwnerAssociationObserver;
use App\Observers\PostLikeObserver;
use App\Observers\ProposalObserver;
use App\Observers\ResidentialFormObserver;
use App\Observers\SaleNOCObserver;
use App\Observers\TechnicianAssetsObserver;
use App\Observers\TenderObserver;
use App\Observers\UserObserver;
use App\Observers\WDAObserver;
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
