<?php

namespace App\Providers;

use App\Filament\Pages\BudgetVsActual;
use App\Filament\Pages\Documents;
use App\Filament\Pages\ReserveFundStatement;
use App\Filament\Pages\TrialBalance;
use App\Jobs\Residentapproval;
use App\Models\Accounting\Budget;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\Proposal;
use App\Models\Accounting\Tender;
use App\Models\Accounting\WDA;
use App\Models\AgingReport;
use App\Models\ApartmentOwner;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\Document;
use App\Models\Building\FacilityBooking;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\Community\Poll;
use App\Models\Community\Post;
use App\Models\CoolingAccount;
use App\Models\DelinquentOwner;
use App\Models\FamilyMember;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Forms\Guest;
use App\Models\Forms\MoveInOut;
use App\Models\Forms\SaleNOC;
use App\Models\Gatekeeper\Patrolling;
use App\Models\GeneralFund;
use App\Models\Item;
use App\Models\ItemInventory;
use App\Models\Master\Facility;
use App\Models\Master\Service;
use App\Models\MollakTenant;
use App\Models\OacomplaintReports;
use App\Models\OwnerAssociation;
use App\Models\OwnerAssociationInvoice;
use App\Models\OwnerAssociationReceipt;
use App\Models\ResidentialForm;
use App\Models\TechnicianAssets;
use App\Models\User\User;
use App\Models\UserApproval;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatVisitor;
use App\Policies\Accounting\BudgetPolicy;
use App\Policies\Accounting\InvoicePolicy;
use App\Policies\Accounting\OAMInvoicePolicy;
use App\Policies\Accounting\ProposalPolicy;
use App\Policies\Accounting\TenderPolicy;
use App\Policies\Accounting\WDAPolicy;
use App\Policies\AgingReportPolicy;
use App\Policies\ApartmentOwnerPolicy;
use App\Policies\AssetMaintenancePolicy;
use App\Policies\AssetPolicy;
use App\Policies\Building\BuildingPolicy;
use App\Policies\Building\ComplaintPolicy;
use App\Policies\Building\DocumentPolicy;
use App\Policies\Building\FacilityBookingPolicy;
use App\Policies\Building\FlatPolicy;
use App\Policies\Building\FlatTenantPolicy;
use App\Policies\Community\PollPolicy;
use App\Policies\Community\PostPolicy;
use App\Policies\CoolingAccountPolicy;
use App\Policies\DelinquentOwnerPolicy;
use App\Policies\FacilityPolicy;
use App\Policies\FamilyMemberPolicy;
use App\Policies\Forms\AccessCardPolicy;
use App\Policies\Forms\FitOutFormPolicy;
use App\Policies\Forms\GuestPolicy;
use App\Policies\Forms\MoveInOutPolicy;
use App\Policies\Forms\SaleNOCPolicy;
use App\Policies\Gatekeeper\PatrollingPolicy;
use App\Policies\ItemInventoryPolicy;
use App\Policies\ItemPolicy;
use App\Policies\Master\ServicePolicy;
use App\Policies\MollakTenantPolicy;
use App\Policies\OacomplaintReportsPolicy;
use App\Policies\OwnerAssociationInvoicePolicy;
use App\Policies\OwnerAssociationPolicy;
use App\Policies\OwnerAssociationReceiptPolicy;
use App\Policies\ResidentialFormPolicy;
use App\Policies\TechnicianAssetsPolicy;
use App\Policies\User\UserPolicy;
use App\Policies\UserApprovalPolicy;
use App\Policies\Vendor\ContractPolicy;
use App\Policies\Vendor\VendorPolicy;
use App\Policies\Visitor\FlatVisitorPolicy;
use App\Tables\Columns\VendorService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Post::class => PostPolicy::class,
        // FacilityBooking::class => FacilityPolicy::class,
        AccessCard::class => AccessCardPolicy::class,
        MollakTenant::class => MollakTenantPolicy::class,
        UserApproval::class => UserApprovalPolicy::class,
        OwnerAssociation::class => OwnerAssociationPolicy::class,
        Document::class => DocumentPolicy::class,
        Facility::class => FacilityPolicy::class,
        Service::class => ServicePolicy::class,
        // VendorService::class
        User::class => UserPolicy::class,
        // Documents::class => DocumentsPolicy::class,
        Building::class => BuildingPolicy::class,
        Flat::class => FlatPolicy::class,
        FacilityBooking::class => FacilityBookingPolicy::class,
        // ServiceBooking
        Patrolling::class => PatrollingPolicy::class,
        OacomplaintReports::class => OacomplaintReportsPolicy::class,
        ApartmentOwner::class => ApartmentOwnerPolicy::class,
        MollakTenant::class => MollakTenantPolicy::class,
        Vendor::class => VendorPolicy::class,
        Contract::class =>ContractPolicy::class,
        WDA::class => WDAPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Tender::class => TenderPolicy::class,
        Proposal::class => ProposalPolicy::class,
        TechnicianAssets::class =>TechnicianAssetsPolicy::class,
        Asset::class => AssetPolicy::class,
        AssetMaintenance::class => AssetMaintenancePolicy::class,
        Budget::class => BudgetPolicy::class,
        // BudgetVsActual::class => BudgetVsActualPolicy::class,
        DelinquentOwner::class => DelinquentOwnerPolicy::class,
        AgingReport::class => AgingReportPolicy::class,
        // Bank
        // GeneralFund::class => GeneralFundPolicy::class,
        // ReserveFundStatement::class => ReserveFundStatement::class,
        // Generate
        OwnerAssociationInvoice::class => OwnerAssociationInvoicePolicy::class,
        OwnerAssociationReceipt::class => OwnerAssociationReceiptPolicy::class,
        // TrialBalance::class =>trialBalancePolicy::class,
        // Mollack
        OAMInvoice::class => OAMInvoicePolicy::class,
        CoolingAccount::class => CoolingAccountPolicy::class,
        Guest::class => GuestPolicy::class,
        // MoveInOut::class => MoveInOutPolicy::class,
        AccessCard::class => AccessCardPolicy::class,
        ResidentialForm::class => ResidentialFormPolicy::class,
        SaleNOC::class => SaleNOCPolicy::class,
        FitOutForm::class => FitOutFormPolicy::class,
        FlatVisitor::class => FlatVisitorPolicy::class,
        Poll::class => PollPolicy::class,
        FlatTenant::class => FlatTenantPolicy::class,
        Item::class => ItemPolicy::class,
        ItemInventory::class => ItemInventoryPolicy::class,
        FamilyMember::class => FamilyMemberPolicy::class,
        // Complaint::class => ComplaintPolicy::class,
        
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Automatically finding the Policies
        Gate::guessPolicyNamesUsing(function ($modelClass) {
            return 'App\\Policies\\' . class_basename($modelClass) . 'Policy';
        });

        $this->registerPolicies();
    }
}
