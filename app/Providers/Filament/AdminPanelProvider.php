<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use App\Models\User\User;
use Illuminate\View\View;
use App\Helpers\UrlHelper;
use App\Models\AgingReport;
use App\Models\Master\Role;
use Filament\PanelProvider;
use App\Models\OwnerAssociation;
// use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use App\Filament\Resources\WDAResource;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\DemoResource;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\ActivityResource;
use App\Filament\Resources\IncidentResource;
use App\Filament\Resources\User\UserResource;
use App\Filament\Resources\PatrollingResource;
use App\Filament\Resources\AgingReportResource;
use App\Filament\Resources\LegalNoticeResource;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Resources\FamilyMemberResource;
use App\Filament\Resources\LegalOfficerResource;
use App\Filament\Resources\UserApprovalResource;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Resources\BankStatementResource;
use App\Filament\Resources\Master\CountryResource;
use App\Filament\Components\NavigationItemExtended;
use App\Filament\Resources\DelinquentOwnerResource;
use App\Filament\Resources\AssetMaintenanceResource;
use App\Filament\Resources\BuildingEngineerResource;
use App\Filament\Resources\ComplaintOfficerResource;
use App\Filament\Resources\NotificationListResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Filament\Resources\OacomplaintReportsResource;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
// use Filament\Pages\Dashboard;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use App\Filament\Resources\OwnerAssociationInvoiceResource;
use App\Filament\Resources\OwnerAssociationReceiptResource;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->tenant(OwnerAssociation::class, ownershipRelationship: 'ownerAssociation', slugAttribute: 'slug')
            ->tenantDomain('{tenant:slug}.' . env('APP_URL'))
            ->login(Login::class)
            ->resources([
                config('filament-logger.activity_resource')
            ])
            ->profile(EditProfile::class)
            ->colors([

                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Orange,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'pink' => Color::Pink
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
            ])
            ->favicon(asset('images/favicon.png'))
            ->darkMode(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->sidebarCollapsibleOnDesktop()
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                // if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
                $builder->groups([
                    NavigationGroup::make('Dashboard')
                        ->items([
                            NavigationItem::make('Dashboard')
                                ->icon('heroicon-o-home')
                                ->activeIcon('heroicon-s-home')
                                ->url('/admin'),
                        ])
                        ->collapsed(true),
                ]);
                // }
                $user = User::find(auth()->user()->id);
                if (
                    $user->can('view_any_mollak::tenant') ||
                    $user->can('view_any_user::approval') ||
                    $user->can('view_any_owner::association') ||
                    $user->can('view_any_tenant::document') ||
                    $user->can('view_any_master::facility') ||
                    $user->can('view_any_master::service') ||
                    $user->can('view_any_master::vendor::service') ||
                    $user->can('view_any_user::user') ||
                    $user->can('view_any_building::documents') ||
                    $user->can('page_Documents') ||
                    auth()->user()->role_id == 10
                ) {
                    $builder->groups([
                        NavigationGroup::make('Master')
                            ->items([
                                NavigationItem::make('Tenants')
                                    ->url('/admin/mollak-tenants')
                                    // ->makeCustomUrl('/mollak-tenants')
                                    ->hidden(!$user->can('view_any_mollak::tenant'))
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(1),
                                NavigationItem::make('Resident Approval')
                                    ->url(UserApprovalResource::getUrl('index'))
                                    // ->makeCustomUrl("/user-approvals")
                                    ->hidden(!$user->can('view_any_user::approval'))
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(2),
                                // NavigationItem::make('MD')
                                //     ->url('/admin/m-d-s')
                                //     ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                //     ->icon('heroicon-o-users')
                                //     ->activeIcon('heroicon-o-users')
                                //     ->sort(3),
                                // NavigationItem::make('Accounts Manager')
                                //     ->url('/admin/accounts-managers')
                                //     ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                //     ->icon('heroicon-o-users')
                                //     ->activeIcon('heroicon-o-users')
                                //     ->sort(4),
                                // NavigationItem::make('Building Engineer')
                                //     ->url(BuildingEngineerResource::getUrl('index'))
                                //     ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                //     ->icon('heroicon-o-users')
                                //     ->activeIcon('heroicon-o-users')
                                //     ->sort(5),
                                // NavigationItem::make('Complaint Officer')
                                //     ->url(ComplaintOfficerResource::getUrl('index'))
                                //     ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                //     ->icon('heroicon-o-users')
                                //     ->activeIcon('heroicon-o-users')
                                //     ->sort(6),
                                // NavigationItem::make('Legal Officer')
                                //     ->url(LegalOfficerResource::getUrl('index'))
                                //     ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                //     ->icon('heroicon-o-users')
                                //     ->activeIcon('heroicon-o-users')
                                //     ->sort(7),
                                // NavigationItem::make('Medias')
                                //     ->url('/admin/media')
                                //     // ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                                //     ->icon('heroicon-m-photo')
                                //     ->activeIcon('heroicon-m-photo')
                                //     ->sort(2),
                                NavigationItem::make('Owner association')
                                    ->url('/admin/owner-associations')
                                    // ->makeCustomUrl('/owner-associations')
                                    ->hidden(!$user->can('view_any_owner::association'))
                                    ->icon('heroicon-s-user-group')
                                    ->activeIcon('heroicon-s-user-group')
                                    ->sort(8),
                                // NavigationItem::make('Cities')
                                //     // ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                                //     ->url('/admin/master/cities')
                                //     ->icon('heroicon-m-globe-americas')
                                //     ->activeIcon('heroicon-m-globe-americas')
                                //     ->sort(4),
                                NavigationItem::make('Resident documents')
                                    ->url('/admin/tenant-documents')
                                    // ->makeCustomUrl('/tenant-documents')
                                    ->hidden(!$user->can('view_any_tenant::document'))
                                    ->icon('heroicon-o-user-circle')
                                    ->activeIcon('heroicon-o-user-circle')
                                    ->sort(9),
                                NavigationItem::make('Facilities')
                                    ->label('Amenities')
                                    ->hidden(!$user->can('view_any_master::facility'))
                                    // ->makeCustomUrl('/master/facilities')
                                    ->url('/admin/master/facilities')
                                    ->icon('heroicon-o-cube-transparent')
                                    ->activeIcon('heroicon-o-cube-transparent')
                                    ->sort(10),
                                NavigationItem::make('Roles')
                                    ->hidden(function () {
                                        $userRoleId = auth()->user()->role_id;
                                        $adminRoleIds = Role::whereIn('name', ['OA', 'MD'])->pluck('id')->toArray();

                                        return !in_array($userRoleId, $adminRoleIds);
                                    })
                                    ->url('/admin/shield/roles')
                                    ->icon('heroicon-s-user-group')
                                    ->activeIcon('heroicon-s-user-group')
                                    ->sort(11),
                                NavigationItem::make('In-house services')
                                    ->label('Personal services')
                                    ->hidden(!$user->can('view_any_master::service'))
                                    ->url('/admin/master/services')
                                    ->icon('heroicon-m-wrench')
                                    ->activeIcon('heroicon-m-wrench')
                                    ->sort(12),
                                NavigationItem::make('Vendor services')
                                    ->hidden(!$user->can('view_any_master::vendor::service'))
                                    ->url('/admin/master/vendor-services')
                                    // ->makeCustomUrl('/vendor-services')
                                    ->icon('heroicon-m-wrench-screwdriver')
                                    ->activeIcon('heroicon-m-wrench-screwdriver')
                                    ->sort(13),
                                NavigationItem::make('Users')
                                    ->hidden(!$user->can('view_any_user::user'))
                                    ->url(UserResource::getUrl('index'))
                                    ->icon('heroicon-s-user-group')
                                    ->activeIcon('heroicon-s-user-group')
                                    ->sort(14),
                                // NavigationItem::make('Documents')
                                //     ->hidden(!$user->can('page_Documents'))
                                //     ->url('/admin/documents')
                                //     ->icon('heroicon-s-document-text')
                                //     ->activeIcon('heroicon-s-document-text')
                                //     ->sort(15),
                            ])
                            ->collapsed(true),
                    ]);
                }


                if (
                    $user->can('view_any_building::building') ||
                    $user->can('view_any_building::flat') ||
                    $user->can('view_any_building::facility::booking') ||
                    $user->can('view_any_building::service::booking')  ||
                    $user->can('view_any_patrolling') ||
                    $user->can('view_any_oacomplaint::reports')
                ) {
                    $builder->groups([
                        NavigationGroup::make('Property management')
                            ->items([
                                NavigationItem::make('Buildings')
                                    ->url('/admin/building/buildings')
                                    ->visible($user->can('view_any_building::building'))
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(1),
                                NavigationItem::make('Units')
                                    ->url('/admin/flats')
                                    ->visible($user->can('view_any_building::flat'))
                                    ->icon('heroicon-o-home')
                                    ->activeIcon('heroicon-o-home')
                                    ->sort(2),
                                NavigationItem::make('Facility bookings')
                                    ->label('Amenity Bookings')
                                    ->url('/admin/building/facility-bookings')
                                    ->visible($user->can('view_any_building::facility::booking'))
                                    ->icon('heroicon-o-cube-transparent')
                                    ->activeIcon('heroicon-o-cube-transparent')
                                    ->sort(3),
                                NavigationItem::make('Personal Service Bookings')
                                    ->url('/admin/building/service-bookings')
                                    ->visible($user->can('view_any_building::service::booking'))
                                    ->icon('heroicon-m-wrench')
                                    ->activeIcon('heroicon-m-wrench')
                                    ->sort(4),
                                NavigationItem::make('Patrollings')
                                    ->url(PatrollingResource::getUrl('index'))
                                    ->visible($user->can('view_any_patrolling'))
                                    ->icon('heroicon-o-magnifying-glass-circle')
                                    ->activeIcon('heroicon-o-magnifying-glass-circle')
                                    ->sort(5),
                                NavigationItem::make('OA Complaint Reports')
                                    ->url(OacomplaintReportsResource::getUrl('index'))
                                    ->visible($user->can('view_any_oacomplaint::reports'))
                                    ->icon('heroicon-c-clipboard-document')
                                    ->activeIcon('heroicon-c-clipboard-document')
                                    ->sort(6),
                            ])->collapsed(),
                    ]);
                }
                // || Role::where('id', auth()->user()->role_id)->first()->name != 'Admin'
                if ($user->can('view_any_user::owner') || $user->can('view_any_user::tenant') || $user->can('view_any_vehicle')) {
                    $builder->groups([
                        NavigationGroup::make('User management')
                            ->items([
                                NavigationItem::make('Owners')
                                    ->url('/admin/user/owners')
                                    ->visible($user->can('view_any_user::owner'))
                                    ->icon('heroicon-o-user')
                                    ->activeIcon('heroicon-o-user')
                                    ->sort(1),
                                NavigationItem::make('Tenants')
                                    ->url('/admin/user/tenants')
                                    ->visible($user->can('view_any_user::tenant'))
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(2),
                                NavigationItem::make('Vehicles')
                                    ->url(VehicleResource::getUrl('index'))
                                    ->visible($user->can('view_any_vehicle'))
                                    ->icon('heroicon-m-building-office-2')
                                    ->activeIcon('heroicon-m-building-office-2')
                                    ->sort(3),
                            ])
                            ->collapsed(true),
                    ]);
                }


                // $builder->groups([
                //     NavigationGroup::make('Location Management')
                //         ->items([
                //             NavigationItem::make('Country')
                //                 ->url(CountryResource::getUrl('index'))
                //                 ->icon('heroicon-o-map-pin')
                //                 ->activeIcon('heroicon-o-map-pin')
                //                 ->sort(1),
                //             NavigationItem::make('State')
                //                 ->url('/admin/posts')
                //                 ->icon('heroicon-o-map-pin')
                //                 ->activeIcon('heroicon-o-map-pin')
                //                 ->sort(2),
                //             NavigationItem::make('City')
                //                 ->url('/admin/polls')
                //                 ->icon('heroicon-o-map-pin')
                //                 ->activeIcon('heroicon-o-map-pin')
                //                 ->sort(3),
                //         ])
                //         ->collapsed(true),
                // ]);

                if (
                    $user->can('view_any_vendor::vendor') ||
                    $user->can('view_any_contract') ||
                    $user->can('view_any_w::d::a') ||
                    $user->can('view_any_invoice') ||
                    $user->can('view_any_tender') ||
                    $user->can('view_any_proposal') ||
                    $user->can('view_any_technician::assets') ||
                    $user->can('view_any_asset') ||
                    $user->can('view_any_asset::maintenance')
                ) {
                    $builder->groups([
                        NavigationGroup::make('Vendor management')
                            ->items([
                                NavigationItem::make('Vendor')
                                    ->url('/admin/vendor/vendors')
                                    ->hidden(!$user->can('view_any_vendor::vendor'))
                                    ->icon('heroicon-m-user-circle')
                                    ->activeIcon('heroicon-m-user-circle')
                                    ->sort(1),
                                NavigationItem::make('Contract')
                                    ->url('/admin/contracts')
                                    ->hidden(!$user->can('view_any_contract'))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->activeIcon('heroicon-o-clipboard-document')
                                    ->sort(2),
                                NavigationItem::make('WDA')
                                    ->url('/admin/w-d-a-s')
                                    ->hidden(!$user->can('view_any_w::d::a'))
                                    ->icon('heroicon-o-chart-bar-square')
                                    ->activeIcon('heroicon-o-chart-bar-square')
                                    ->sort(3),
                                NavigationItem::make('Invoice')
                                    ->url('/admin/invoices')
                                    ->hidden(!$user->can('view_any_invoice'))
                                    ->icon('heroicon-o-document-arrow-up')
                                    ->activeIcon('heroicon-o-document-arrow-up')
                                    ->sort(4),
                                NavigationItem::make('Tenders')
                                    ->url('/admin/tenders')
                                    ->hidden(!$user->can('view_any_tender'))
                                    ->icon('heroicon-s-document-text')
                                    ->activeIcon('heroicon-s-document-text')
                                    ->sort(5),
                                NavigationItem::make('Proposals')
                                    ->url('/admin/proposals')
                                    ->hidden(!$user->can('view_any_proposal'))
                                    ->icon('heroicon-s-gift-top')
                                    ->activeIcon('heroicon-s-gift-top')
                                    ->sort(6),
                                NavigationItem::make('Technician assets')
                                    ->url('/admin/technician-assets')
                                    ->hidden(!$user->can('view_any_technician::assets'))
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(7),
                                NavigationItem::make('Assets')
                                    ->url('/admin/assets')
                                    ->hidden(!$user->can('view_any_asset'))
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->activeIcon('heroicon-o-rectangle-stack')
                                    ->sort(8),
                                NavigationItem::make('Assets Maintenance')
                                    ->url(AssetMaintenanceResource::getUrl('index'))
                                    ->hidden(!$user->can('view_any_asset::maintenance'))
                                    ->icon('heroicon-s-document-magnifying-glass')
                                    ->activeIcon('heroicon-s-document-magnifying-glass')
                                    ->sort(9),
                            ])
                            ->collapsed(true),
                    ]);
                }

                if (
                    $user->can('view_any_budget') ||
                    $user->can('page_BudgetVsActual') ||
                    $user->can('view_any_delinquent::owner') ||
                    $user->can('view_any_aging::report') ||
                    $user->can('view_any_bank::statement') ||
                    $user->can('page_GeneralFundStatement') ||
                    $user->can('page_ReserveFundStatement') ||
                    $user->can('view_any_owner::association::invoice') ||
                    $user->can('view_any_owner::association::receipt') ||
                    $user->can('page_TrialBalance') ||
                    $user->can('page_GeneralFundStatementMollak') ||
                    $user->can('page_ReserveFundStatementMollak')
                ) {
                    $builder->groups([
                        NavigationGroup::make('Financial Report')
                            ->items([
                                NavigationItem::make('Budget')
                                    ->url('/admin/budgets')
                                    ->hidden(!$user->can('view_any_budget'))
                                    ->icon('heroicon-o-currency-dollar')
                                    ->activeIcon('heroicon-o-currency-dollar')
                                    ->sort(1),
                                NavigationItem::make('Budget vs Actual')
                                    ->url('/admin/budget-vs-actual')
                                    ->hidden(!$user->can('page_BudgetVsActual'))
                                    ->icon('heroicon-s-pencil-square')
                                    ->activeIcon('heroicon-s-pencil-square')
                                    ->sort(2),
                                NavigationItem::make('Delinquent owners')
                                    ->url(DelinquentOwnerResource::getUrl('index'))
                                    ->hidden(!$user->can('view_any_delinquent::owner'))
                                    ->icon('heroicon-s-bars-arrow-down')
                                    ->activeIcon('heroicon-s-bars-arrow-down')
                                    ->sort(3),
                                NavigationItem::make('Aging report')
                                    ->url(AgingReportResource::getUrl('index'))
                                    ->hidden(!$user->can('view_any_aging::report'))
                                    ->icon('heroicon-o-document')
                                    ->activeIcon('heroicon-o-document')
                                    ->sort(4),
                                NavigationItem::make('Receipts')
                                    ->url(BankStatementResource::getUrl('index'))
                                    ->hidden(!$user->can('view_any_bank::statement'))
                                    ->icon('heroicon-s-document-text')
                                    ->activeIcon('heroicon-s-document-text')
                                    ->sort(5),
                                NavigationItem::make('General Fund Statement')
                                    ->url('/admin/general-fund-statement')
                                    ->hidden(!$user->can('page_GeneralFundStatement'))
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(6),
                                NavigationItem::make('Reserve Fund Statement')
                                    ->url('/admin/reserve-fund-statement')
                                    ->hidden(!$user->can('page_ReserveFundStatement'))
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->activeIcon('heroicon-m-clipboard-document-list')
                                    ->sort(7),
                                NavigationItem::make('Generate Invoice')
                                    ->url(OwnerAssociationInvoiceResource::getUrl('index'))
                                    ->hidden(!$user->can('view_any_owner::association::invoice'))
                                    ->icon('heroicon-s-document-arrow-up')
                                    ->activeIcon('heroicon-s-document-arrow-up')
                                    ->sort(8),
                                NavigationItem::make('Generate Receipt')
                                    ->url(OwnerAssociationReceiptResource::getUrl('index'))
                                    ->hidden(!$user->can('view_any_owner::association::receipt'))
                                    ->icon('heroicon-s-document-arrow-down')
                                    ->activeIcon('heroicon-s-document-arrow-down')
                                    ->sort(9),
                                NavigationItem::make('Trial Balance')
                                    ->url('/admin/trial-balance')
                                    ->hidden(!$user->can('page_TrialBalance'))
                                    ->icon('heroicon-s-clipboard-document')
                                    ->activeIcon('heroicon-s-clipboard-document')
                                    ->sort(10),
                                // NavigationItem::make('General Fund Statement Bank Book')
                                //     ->url('/admin/mollak-general-fund-statement')
                                //     ->hidden(!$user->can('page_GeneralFundStatementMollak'))
                                //     ->icon('heroicon-m-clipboard-document-check')
                                //     ->activeIcon('heroicon-m-clipboard-document-check')
                                //     ->sort(11),
                                // NavigationItem::make('Reserve Fund Statement Bank Book')
                                //     ->url('/admin/mollak-reserve-fund-statement')
                                //     ->hidden(!$user->can('page_ReserveFundStatementMollak'))
                                //     ->icon('heroicon-m-clipboard-document-list')
                                //     ->activeIcon('heroicon-m-clipboard-document-list')
                                //     ->sort(12),

                            ])
                            ->collapsed(true),
                    ]);
                }
                if ($user->can('view_any_ledgers') || $user->can('view_any_vendor::ledgers') || $user->can('view_any_cooling::account')) {
                    $builder->groups([
                        NavigationGroup::make('Reports')
                            ->items([
                                NavigationItem::make('Service charge ledgers')
                                    ->url('/admin/ledgers')
                                    ->hidden(!$user->can('view_any_ledgers'))
                                    ->icon('heroicon-m-list-bullet')
                                    ->activeIcon('heroicon-m-list-bullet')
                                    ->sort(1),
                                NavigationItem::make('Service provider ledgers')
                                    ->url('/admin/vendor-ledgers')
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->hidden(!$user->can('view_any_vendor::ledgers'))
                                    ->activeIcon('heroicon-o-rectangle-stack')
                                    ->sort(2),
                                NavigationItem::make('Cooling account')
                                    ->url('/admin/cooling-accounts')
                                    ->hidden(!$user->can('view_any_cooling::account'))
                                    ->icon('heroicon-o-cube-transparent')
                                    ->activeIcon('heroicon-o-cube-transparent')
                                    ->sort(3),
                            ])
                            ->collapsed(true),
                    ]);
                }

                if (
                    $user->can('view_any_guest::registration') ||
                    $user->can('view_any_move::in::forms::document') ||
                    $user->can('view_any_move::out::forms::document') ||
                    $user->can('view_any_fit::out::forms::document') ||
                    $user->can('view_any_access::card::forms::document') ||
                    $user->can('view_any_residential::form') ||
                    $user->can('view_any_noc::form') ||
                    $user->can('view_any_visitor::form') ||
                    $user->can('view_any_family::member')
                ) {
                    $builder->groups([
                        //DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false view_any_building::building
                        NavigationGroup::make('Request Forms')
                            ->items([
                                NavigationItem::make('Holiday Homes Guest registration')
                                    ->url('/admin/guest-registrations')
                                    ->hidden(!$user->can('view_any_guest::registration'))
                                    ->icon('heroicon-m-identification')
                                    ->activeIcon('heroicon-m-identification')
                                    ->sort(1),
                                NavigationItem::make('Move in')
                                    ->url('/admin/move-in-forms-documents')
                                    ->hidden(!$user->can('view_any_move::in::forms::document'))
                                    ->icon('heroicon-s-arrow-right-circle')
                                    ->activeIcon('heroicon-s-arrow-right-circle')
                                    ->sort(2),
                                NavigationItem::make('Move out')
                                    ->url('/admin/move-out-forms-documents')
                                    ->hidden(!$user->can('view_any_move::out::forms::document'))
                                    ->icon('heroicon-s-arrow-left-circle')
                                    ->activeIcon('heroicon-s-arrow-left-circle')
                                    ->sort(3),
                                NavigationItem::make('Fitout')
                                    ->url('/admin/fit-out-forms-documents')
                                    ->hidden(!$user->can('view_any_fit::out::forms::document'))
                                    ->icon('heroicon-s-face-smile')
                                    ->activeIcon('heroicon-s-face-smile')
                                    ->sort(4),
                                NavigationItem::make('Access card')
                                    ->url('/admin/access-card-forms-documents')
                                    ->hidden(!$user->can('view_any_access::card::forms::document'))
                                    ->icon('heroicon-s-rectangle-stack')
                                    ->activeIcon('heroicon-s-rectangle-stack')
                                    ->sort(5),
                                NavigationItem::make('Residential')
                                    ->url('/admin/residential-forms')
                                    ->hidden(!$user->can('view_any_residential::form'))
                                    ->icon('heroicon-s-building-library')
                                    ->activeIcon('heroicon-s-building-library')
                                    ->sort(6),
                                NavigationItem::make('Sale NOC')
                                    ->url('/admin/noc-forms')
                                    ->hidden(!$user->can('view_any_noc::form'))
                                    ->icon('heroicon-m-shopping-cart')
                                    ->activeIcon('heroicon-m-shopping-cart')
                                    ->sort(7),
                                NavigationItem::make('Visitors')
                                    ->url('/admin/visitor-forms')
                                    ->hidden(!$user->can('view_any_visitor::form'))
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(8),
                                NavigationItem::make('Family Members')
                                    ->url(FamilyMemberResource::getUrl('index'))
                                    ->visible($user->can('view_any_family::member'))
                                    ->icon('heroicon-s-user-group')
                                    ->activeIcon('heroicon-s-user-group')
                                    ->sort(9),
                            ])
                            ->collapsed(true),
                    ]);
                }

                if ($user->can('view_any_announcement') || $user->can('view_any_post') || $user->can('view_any_poll')) {
                    $builder->groups([
                        NavigationGroup::make('Community')
                            ->items([
                                NavigationItem::make('Notice boards')
                                    ->url('/admin/announcements')
                                    ->icon('heroicon-o-megaphone')
                                    ->activeIcon('heroicon-o-megaphone')
                                    ->visible($user->can('view_any_announcement'))
                                    ->sort(1),
                                NavigationItem::make('Posts')
                                    ->url('/admin/posts')
                                    ->icon('heroicon-m-photo')
                                    ->activeIcon('heroicon-m-photo')
                                    ->visible($user->can('view_any_post'))
                                    ->sort(2),
                                NavigationItem::make('Polls')
                                    ->url('/admin/polls')
                                    ->icon('heroicon-s-hand-thumb-up')
                                    ->visible($user->can('view_any_poll'))
                                    ->activeIcon('heroicon-s-hand-thumb-up')
                                    ->sort(3),
                            ])
                            ->collapsed(true),
                    ]);
                }

                if ($user->can('view_any_building::flat::tenant')) {
                    $builder->groups([
                        NavigationGroup::make('Unit management')
                            ->items([
                                NavigationItem::make('Residents')
                                    ->url('/admin/building/flat-tenants')
                                    ->icon('heroicon-o-user-circle')
                                    ->hidden(!$user->can('view_any_building::flat::tenant'))
                                    ->activeIcon('heroicon-o-user-circle')
                                    ->sort(1),
                            ])
                            ->collapsed(true),
                    ]);
                }

                if ($user->can('view_any_item') || $user->can('view_any_item::inventory')) {
                    $builder->groups([
                        NavigationGroup::make('Inventory Management')
                            ->items([
                                NavigationItem::make('Items')
                                    ->url('/admin/items')
                                    ->hidden(!$user->can('view_any_item'))
                                    ->icon('heroicon-m-rectangle-group')
                                    ->activeIcon('heroicon-m-rectangle-group')
                                    ->sort(1),
                                NavigationItem::make('Item inventory')
                                    ->url('/admin/item-inventories')
                                    ->hidden(!$user->can('view_any_item::inventory'))
                                    ->icon('heroicon-m-arrow-down-on-square-stack')
                                    ->activeIcon('heroicon-m-arrow-down-on-square-stack')
                                    ->sort(2),
                            ])
                            ->collapsed(true),
                    ]);
                }
                // if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
                //     $builder->groups([
                //         NavigationGroup::make('Document Management')
                //             ->items([
                //                 NavigationItem::make('Buildings')
                //                     ->url('/admin/building-documents')
                //                     ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                //                     ->icon('heroicon-o-building-office-2')
                //                     ->activeIcon('heroicon-o-building-office-2')
                //                     ->sort(1),
                //                 NavigationItem::make('Units')
                //                     ->url('/admin/flat-documents')
                //                     ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                //                     ->icon('heroicon-o-home')
                //                     ->activeIcon('heroicon-o-home')
                //                     ->sort(2),
                //             ]),
                //     ]);
                // }


                if ($user->can('view_any_complaintscomplaint') || $user->can('view_any_complaintsenquiry') || $user->can('view_any_complaintssuggession')) {
                    $builder->groups([
                        NavigationGroup::make('Happiness center')
                            ->items([
                                NavigationItem::make('Complaints')
                                    ->url('/admin/complaintscomplaints')
                                    ->hidden(!$user->can('view_any_complaintscomplaint'))
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->activeIcon('heroicon-m-clipboard-document-list')
                                    ->sort(1),
                                NavigationItem::make('Enquiries')
                                    ->url('/admin/complaintsenquiries')
                                    ->hidden(!$user->can('view_any_complaintsenquiry'))
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(2),
                                NavigationItem::make('Suggestions')
                                    ->url('/admin/complaintssuggessions')
                                    ->hidden(!$user->can('view_any_complaintssuggession'))
                                    ->icon('heroicon-s-pencil-square')
                                    ->activeIcon('heroicon-s-pencil-square')
                                    ->sort(3),
                            ])
                            ->collapsed(true),
                    ]);
                }
                if ($user->can('view_any_helpdeskcomplaint')) {
                    $builder->groups([
                        NavigationGroup::make('Facility Support')
                            ->items([
                                NavigationItem::make('Issues')
                                    ->url('/admin/helpdeskcomplaints')
                                    ->hidden(!$user->can('view_any_helpdeskcomplaint'))
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->activeIcon('heroicon-m-clipboard-document-list')
                                    ->sort(1),
                            ])
                            ->collapsed(true),
                    ]);
                }
                if ($user->can('view_any_snags') || $user->can('view_any_incident')) {
                    $builder->groups([
                        NavigationGroup::make('Security')
                            ->items([
                                NavigationItem::make('Snags')
                                    ->url('/admin/snags')
                                    ->hidden(!$user->can('view_any_snags'))
                                    ->icon('heroicon-s-swatch')
                                    ->activeIcon('heroicon-s-swatch')
                                    ->sort(1),
                                NavigationItem::make('Incidents')
                                    ->url(IncidentResource::getUrl('index'))
                                    ->hidden(!$user->can('view_any_incident'))
                                    ->icon('heroicon-c-map-pin')
                                    ->activeIcon('heroicon-c-map-pin')
                                    ->sort(2),
                            ])
                            ->collapsed(true),
                    ]);
                }
                // if ($user->can('view_any_legal::notice')){
                //     $builder->groups([
                //         NavigationGroup::make('Leagal Notice')
                //             ->items([
                //                 NavigationItem::make('Leagal Notice')
                //                     ->url(LegalNoticeResource::getUrl('index'))
                //                     ->icon('heroicon-s-swatch')
                //             ])
                //             ->collapsed(true),
                //     ]);
                // }
                if ($user->can('view_any_activity')) {
                    $builder->groups([
                        NavigationGroup::make('Activity')
                            ->items([
                                NavigationItem::make('Activity Log')
                                    ->url(ActivityResource::getUrl('index'))
                                    ->Icon('heroicon-m-clipboard-document-list')
                            ])
                            ->collapsed(true),
                    ]);
                }

                $builder->groups([
                    NavigationGroup::make('Notifications')
                        ->items([
                            NavigationItem::make('Notification')
                                ->url(NotificationListResource::getUrl('index'))
                                ->icon('heroicon-o-bell')
                                ->activeIcon('heroicon-o-bell')
                                ->sort(1),
                        ])
                        ->collapsed(true),
                ]);
                return $builder;
            })
            ->renderHook(
                'panels::footer',
                fn(): View => view('filament.hooks.footer'),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,

            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                // FilamentApexChartsPlugin::make()
            ]);
    }
}
