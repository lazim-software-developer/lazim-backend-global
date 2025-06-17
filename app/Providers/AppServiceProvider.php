<?php

namespace App\Providers;

use HttpService;
use App\Models\Item;
use App\Models\Order;
use App\Models\User\User;
use Illuminate\View\View;
use Filament\Tables\Table;
use App\Models\AppFeedback;
use App\Models\Forms\Guest;
use App\Models\UserApproval;
use App\Models\Forms\SaleNOC;
use App\Models\Vendor\Vendor;
use App\Models\Accounting\WDA;
use App\Models\Community\Post;
use App\Observers\WDAObserver;
use App\Models\Forms\MoveInOut;
use App\Models\ResidentialForm;
use App\Models\Vendor\Contract;
use App\Observers\ItemObserver;
use App\Observers\UserObserver;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\OwnerAssociation;
use App\Models\TechnicianAssets;
use App\Observers\GuestObserver;
use App\Observers\OrderObserver;
use App\Observers\SnagsObserver;
use Filament\Resources\Resource;
use App\Models\Accounting\Tender;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Community\Comment;
use App\Observers\TenderObserver;
use App\Observers\VendorObserver;
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
use App\Models\Gatekeeper\Patrolling;
use App\Observers\AccessCardObserver;
use App\Observers\FitOutFormObserver;
use App\Observers\PatrollingObserver;
use App\Models\Community\PollResponse;
use App\Observers\AppFeedbackObserver;
use Illuminate\Support\Facades\Schema;
use App\Observers\AnnouncementObserver;
use App\Observers\PollResponseObserver;
use App\Observers\UserApprovalObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\Building\FacilityBooking;
use Filament\Tables\Enums\FiltersLayout;
use App\Observers\ResidentialFormObserver;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use App\Observers\OwnerAssociationObserver;
use App\Observers\TechnicianAssetsObserver;
use App\Observers\FacilityServiceBookingObserver;
use App\Services\GenericHttpService;
use App\Services\SessionLocalService;
use App\Services\SessionService;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use App\Filament\MyLogoutResponse;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the SessionService as a singleton
        $this->app->singleton(SessionLocalService::class, function ($app) {
            return new SessionLocalService();
        });

        // Register the GenericHttpService as a singleton
        $this->app->singleton(GenericHttpService::class, function ($app) {
            return new GenericHttpService();
        });


        FilamentView::registerRenderHook(
            'panels::footer',
            fn(): View => view('filament.hooks.footer'),
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
        Vendor::observe(VendorObserver::class);
        Order::observe(OrderObserver::class);
        UserApproval::observe(UserApprovalObserver::class);
        Patrolling::observe(PatrollingObserver::class);
        Item::observe(ItemObserver::class);
        // Complaint::observe(SnagsObserver::class);
        PollResponse::observe(PollResponseObserver::class);
        AppFeedback::observe(AppFeedbackObserver::class);

        // Resource::scopeToTenant(false);
        FilamentIcon::register([
            'panels::topbar.open-database-notifications-button' => view('icons.sidebar-notifications'),
            'panels::topbar.open-sidebar-button' => 'heroicon-o-bars-3', // Use heroicon-o-bars-3 for open sidebar button hamburger icon
            'panels::topbar.close-sidebar-button' => 'heroicon-o-x-mark',
        ]);

        //Global settings for Admin module (for all table per page options)
        Table::configureUsing(function (Table $table): void {
            $table
                ->filtersLayout(FiltersLayout::AboveContentCollapsible)
                ->paginationPageOptions([10, 25, 50]);
        });

        $this->app->bind(LogoutResponseContract::class, MyLogoutResponse::class);
    }
}
