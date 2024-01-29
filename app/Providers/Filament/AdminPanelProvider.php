<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Illuminate\View\View;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\AgingReportResource;
use App\Filament\Resources\BankStatementResource;
use App\Filament\Resources\BuildingEngineerResource;
use App\Filament\Resources\ComplaintOfficerResource;
use App\Filament\Resources\WDAResource;
use App\Filament\Resources\DelinquentOwnerResource;
use App\Filament\Resources\LegalOfficerResource;
use App\Filament\Resources\OwnerAssociationInvoiceResource;
use App\Filament\Resources\OwnerAssociationReceiptResource;
use App\Models\AgingReport;
use App\Models\Master\Role;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(EditProfile::class)
            ->colors([
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'primary' => Color::Indigo,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'pink' => Color::Pink
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
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
                            ]),
                    ]);
                // }

              if(Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'Building Engineer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer'){
                $builder->groups([
                    NavigationGroup::make('Master')
                        ->items([
                            NavigationItem::make('Tenants')
                                ->url('/admin/mollak-tenants')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['Admin','OA']))
                                ->icon('heroicon-o-users')
                                ->activeIcon('heroicon-o-users')
                                ->sort(1),
                            NavigationItem::make('MD')
                                ->url('/admin/m-d-s')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                ->icon('heroicon-o-users')
                                ->activeIcon('heroicon-o-users')
                                ->sort(2),
                            NavigationItem::make('Accounts Manager')
                                ->url('/admin/accounts-managers')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                ->icon('heroicon-o-users')
                                ->activeIcon('heroicon-o-users')
                                ->sort(3),
                            NavigationItem::make('Building Engineer')
                                ->url(BuildingEngineerResource::getUrl('index'))
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                ->icon('heroicon-o-users')
                                ->activeIcon('heroicon-o-users')
                                ->sort(4),
                            NavigationItem::make('Complaint Officer')
                                ->url(ComplaintOfficerResource::getUrl('index'))
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                ->icon('heroicon-o-users')
                                ->activeIcon('heroicon-o-users')
                                ->sort(5),
                            NavigationItem::make('Legal Officer')
                                ->url(LegalOfficerResource::getUrl('index'))
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','MD']))
                                ->icon('heroicon-o-users')
                                ->activeIcon('heroicon-o-users')
                                ->sort(6),
                            // NavigationItem::make('Medias')
                            //     ->url('/admin/media')
                            //     // ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                            //     ->icon('heroicon-m-photo')
                            //     ->activeIcon('heroicon-m-photo')
                            //     ->sort(2),
                            NavigationItem::make('Owner association')
                                ->url('/admin/owner-associations')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['Admin','OA']))
                                ->icon('heroicon-s-user-group')
                                ->activeIcon('heroicon-s-user-group')
                                ->sort(7),
                            // NavigationItem::make('Cities')
                            //     // ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                            //     ->url('/admin/master/cities')
                            //     ->icon('heroicon-m-globe-americas')
                            //     ->activeIcon('heroicon-m-globe-americas')
                            //     ->sort(4),
                            NavigationItem::make('Resident documents')
                                ->url('/admin/tenant-documents')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Admin']))
                                ->icon('heroicon-o-user-circle')
                                ->activeIcon('heroicon-o-user-circle')
                                ->sort(8),
                            NavigationItem::make('Facilities')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Admin']))
                                ->url('/admin/master/facilities')
                                ->icon('heroicon-o-cube-transparent')
                                ->activeIcon('heroicon-o-cube-transparent')
                                ->sort(9),
                            NavigationItem::make('Roles')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Admin']))
                                ->url('/admin/master/roles')
                                ->icon('heroicon-s-user-group')
                                ->activeIcon('heroicon-s-user-group')
                                ->sort(10),
                            NavigationItem::make('In-house services')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Admin']))
                                ->url('/admin/master/services')
                                ->icon('heroicon-m-wrench')
                                ->activeIcon('heroicon-m-wrench')
                                ->sort(11),
                            NavigationItem::make('Vendor services')
                                ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Admin']))
                                ->url('/admin/master/vendor-services')
                                ->icon('heroicon-m-wrench-screwdriver')
                                ->activeIcon('heroicon-m-wrench-screwdriver')
                                ->sort(12),
                        ]),
                ]);
            }
              

                if(Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD'){
                    $builder->groups([
                        NavigationGroup::make('Property management')
                            ->items([
                                NavigationItem::make('Buildings')
                                    ->url('/admin/building/buildings')
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(1),
                                NavigationItem::make('Units')
                                    ->url('/admin/building/flats')
                                    ->icon('heroicon-o-home')
                                    ->activeIcon('heroicon-o-home')
                                    ->sort(2),
                                NavigationItem::make('Facility bookings')
                                    ->url('/admin/building/facility-bookings')
                                    ->icon('heroicon-o-cube-transparent')
                                    ->activeIcon('heroicon-o-cube-transparent')
                                    ->sort(3),
                                // NavigationItem::make('Service Bookings')
                                //     ->url('/admin/building/service-bookings')
                                //     ->icon('heroicon-m-wrench')
                                //     ->activeIcon('heroicon-m-wrench')
                                //     ->sort(4),
                            ]),
                    ]);
                }

                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin' && Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD') {
                    $builder->groups([
                        NavigationGroup::make('User management')
                            ->items([
                                NavigationItem::make('Owners')
                                    ->url('/admin/user/owners')
                                    ->icon('heroicon-o-user')
                                    ->activeIcon('heroicon-o-user')
                                    ->sort(1),
                                NavigationItem::make('Tenants')
                                    ->url('/admin/user/tenants')
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(2),
                            ]),
                    ]);
                }

                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer') {
                    $builder->groups([
                        NavigationGroup::make('Vendor management')
                            ->items([
                                NavigationItem::make('Vendor')
                                    ->url('/admin/vendor/vendors')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer']))
                                    ->icon('heroicon-m-user-circle')
                                    ->activeIcon('heroicon-m-user-circle')
                                    ->sort(1),
                                NavigationItem::make('Contract')
                                    ->url('/admin/contracts')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer']))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->activeIcon('heroicon-o-clipboard-document')
                                    ->sort(2),
                                NavigationItem::make('WDA')
                                    ->url('/admin/w-d-a-s')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer']))
                                    ->icon('heroicon-o-chart-bar-square')
                                    ->activeIcon('heroicon-o-chart-bar-square')
                                    ->sort(3),
                                NavigationItem::make('Invoice')
                                    ->url('/admin/invoices')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer','Accounts Manager','MD']))
                                    ->icon('heroicon-o-document-arrow-up')
                                    ->activeIcon('heroicon-o-document-arrow-up')
                                    ->sort(4),
                                NavigationItem::make('Tenders')
                                    ->url('/admin/tenders')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer']))
                                    ->icon('heroicon-s-document-text')
                                    ->activeIcon('heroicon-s-document-text')
                                    ->sort(5),
                                NavigationItem::make('Proposals')
                                    ->url('/admin/proposals')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer']))
                                    ->icon('heroicon-s-gift-top')
                                    ->activeIcon('heroicon-s-gift-top')
                                    ->sort(6),
                                NavigationItem::make('Technician assets')
                                    ->url('/admin/technician-assets')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer']))
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(7),
                                NavigationItem::make('Assets')
                                    ->url('/admin/assets')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Building Engineer']))
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->activeIcon('heroicon-o-rectangle-stack')
                                    ->sort(8),
                            ]),
                    ]);
                }

                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin' && Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD' && Role::where('id', auth()->user()->role_id)->first()->name != 'Building Engineer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer') {
                    $builder->groups([
                        NavigationGroup::make('Accounting')
                            ->items([
                                NavigationItem::make('Budget')
                                    ->url('/admin/budgets')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-o-currency-dollar')
                                    ->activeIcon('heroicon-o-currency-dollar')
                                    ->sort(1),
                                NavigationItem::make('Budget vs Actual')
                                    ->url('/admin/budget-vs-actual')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-s-pencil-square')
                                    ->activeIcon('heroicon-s-pencil-square')
                                    ->sort(2),
                                NavigationItem::make('Delinquent owners')
                                    ->url(DelinquentOwnerResource::getUrl('index'))
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA','Legal Officer']))
                                    ->icon('heroicon-s-bars-arrow-down')
                                    ->activeIcon('heroicon-s-bars-arrow-down')
                                    ->sort(3),
                                NavigationItem::make('Aging report')
                                    ->url(AgingReportResource::getUrl('index'))
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-o-document')
                                    ->activeIcon('heroicon-o-document')
                                    ->sort(4),
                                NavigationItem::make('Bank Statement')
                                    ->url(BankStatementResource::getUrl('index'))
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-s-document-text')
                                    ->activeIcon('heroicon-s-document-text')
                                    ->sort(5),
                                NavigationItem::make('General Fund Statement')
                                    ->url('/admin/general-fund-statement')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(6),
                                NavigationItem::make('Reserve Fund Statement')
                                    ->url('/admin/reserve-fund-statement')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->activeIcon('heroicon-m-clipboard-document-list')
                                    ->sort(7),
                                NavigationItem::make('Generate Invoice')
                                    ->url(OwnerAssociationInvoiceResource::getUrl('index'))
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-s-document-arrow-up')
                                    ->activeIcon('heroicon-s-document-arrow-up')
                                    ->sort(8),
                                NavigationItem::make('Generate Receipt')
                                    ->url(OwnerAssociationReceiptResource::getUrl('index'))
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-s-document-arrow-down')
                                    ->activeIcon('heroicon-s-document-arrow-down')
                                    ->sort(9),
                                NavigationItem::make('Trial Balance')
                                    ->url('/admin/trial-balance')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-s-clipboard-document')
                                    ->activeIcon('heroicon-s-clipboard-document')
                                    ->sort(10),

                                ]), NavigationGroup::make('Reports')
                            ->items([
                                NavigationItem::make('Service charge ledgers')
                                    ->url('/admin/ledgers')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-m-list-bullet')
                                    ->activeIcon('heroicon-m-list-bullet'),
                                NavigationItem::make('Service provider ledgers')
                                    ->url('/admin/vendor-ledgers')
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->activeIcon('heroicon-o-rectangle-stack'),
                                NavigationItem::make('Cooling account')
                                    ->url('/admin/cooling-accounts')
                                    ->hidden(!in_array(Role::where('id', auth()->user()->role_id)->first()->name, ['OA']))
                                    ->icon('heroicon-o-cube-transparent')
                                    ->activeIcon('heroicon-o-cube-transparent')
                                    ->sort(4),
                            ]),

                    ]);
                }

                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin' && Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer') {
                    $builder->groups([
                        NavigationGroup::make('Forms')
                            ->items([
                                NavigationItem::make('Guest registration')
                                    ->url('/admin/guest-registrations')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-identification')
                                    ->activeIcon('heroicon-m-identification')
                                    ->sort(1),
                                NavigationItem::make('Move in')
                                    ->url('/admin/move-in-forms-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-arrow-right-circle')
                                    ->activeIcon('heroicon-s-arrow-right-circle')
                                    ->sort(2),
                                NavigationItem::make('Move out')
                                    ->url('/admin/move-out-forms-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-arrow-left-circle')
                                    ->activeIcon('heroicon-s-arrow-left-circle')
                                    ->sort(3),
                                NavigationItem::make('Fit out')
                                    ->url('/admin/fit-out-forms-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-face-smile')
                                    ->activeIcon('heroicon-s-face-smile')
                                    ->sort(3),
                                NavigationItem::make('Access card')
                                    ->url('/admin/access-card-forms-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-rectangle-stack')
                                    ->activeIcon('heroicon-s-rectangle-stack')
                                    ->sort(3),
                                NavigationItem::make('Residential')
                                    ->url('/admin/residential-forms')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-building-library')
                                    ->activeIcon('heroicon-s-building-library')
                                    ->sort(4),
                                NavigationItem::make('Sale NOC')
                                    ->url('/admin/noc-forms')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-shopping-cart')
                                    ->activeIcon('heroicon-m-shopping-cart')
                                    ->sort(5),
                                NavigationItem::make('Visitors')
                                    ->url('/admin/visitor-forms')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(6),
                            ]),
                    ]);
                }

               if(Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD' && Role::where('id', auth()->user()->role_id)->first()->name != 'Building Engineer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer'){
                $builder->groups([
                    NavigationGroup::make('Community')
                        ->items([
                            NavigationItem::make('Notice boards')
                                ->url('/admin/announcements')
                                ->icon('heroicon-o-megaphone')
                                ->activeIcon('heroicon-o-megaphone')
                                ->sort(1),
                            NavigationItem::make('Posts')
                                ->url('/admin/posts')
                                ->icon('heroicon-m-photo')
                                ->activeIcon('heroicon-m-photo')
                                ->sort(2),
                            NavigationItem::make('Polls')
                                ->url('/admin/polls')
                                ->icon('heroicon-s-hand-thumb-up')
                                ->activeIcon('heroicon-s-hand-thumb-up')
                                ->sort(3),
                        ]),
                ]);

               }

                if(Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer'){
                    $builder->groups([
                        NavigationGroup::make('Unit management')
                            ->items([
                                NavigationItem::make('Residents')
                                    ->url('/admin/building/flat-tenants')
                                    ->icon('heroicon-o-user-circle')
                                    ->activeIcon('heroicon-o-user-circle')
                                    ->sort(1),
                            ]),
                    ]);
                }

                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin' && Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer') {
                    $builder->groups([
                        NavigationGroup::make('Inventory Management')
                            ->items([
                                NavigationItem::make('Items')
                                    ->url('/admin/items')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-rectangle-group')
                                    ->activeIcon('heroicon-m-rectangle-group')
                                    ->sort(1),
                                NavigationItem::make('Item inventory')
                                    ->url('/admin/item-inventories')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-arrow-down-on-square-stack')
                                    ->activeIcon('heroicon-m-arrow-down-on-square-stack')
                                    ->sort(2),
                            ]),
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


                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin' && Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer') {
                    $builder->groups([
                        NavigationGroup::make('Happiness center')
                            ->items([
                                NavigationItem::make('Complaints')
                                    ->url('/admin/complaintscomplaints')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->activeIcon('heroicon-m-clipboard-document-list')
                                    ->sort(1),
                                NavigationItem::make('Enquiries')
                                    ->url('/admin/complaintsenquiries')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(2),
                                NavigationItem::make('Suggestions')
                                    ->url('/admin/complaintssuggessions')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-pencil-square')
                                    ->activeIcon('heroicon-s-pencil-square')
                                    ->sort(3),
                            ]),
                    ]);
                }
                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin' && Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer') {
                    $builder->groups([
                        NavigationGroup::make('Help desk')
                            ->items([
                                NavigationItem::make('Complaints')
                                    ->url('/admin/helpdeskcomplaints')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-clipboard-document-list')
                                    ->activeIcon('heroicon-m-clipboard-document-list')
                                    ->sort(1),
                            ]),
                    ]);
                }
                if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin' && Role::where('id', auth()->user()->role_id)->first()->name != 'Accounts Manager' && Role::where('id', auth()->user()->role_id)->first()->name != 'MD'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Complaint Officer'&& Role::where('id', auth()->user()->role_id)->first()->name != 'Legal Officer') {
                    $builder->groups([
                        NavigationGroup::make('Security')
                            ->items([
                                NavigationItem::make('Snags')
                                    ->url('/admin/snags')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-swatch')
                                    ->activeIcon('heroicon-s-swatch')
                                    ->sort(1),
                            ]),
                    ]);
                }
                return $builder;
            })
            ->renderHook(
                'panels::footer',
                fn (): View => view('filament.hooks.footer'),
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
            ]);
    }
}
