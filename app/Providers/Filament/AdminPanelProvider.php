<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\DB;
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
            ->viteTheme('resources/css/filament/admin/theme.css')
            // ->colors([
            //     'danger' => Color::Rose,
            //     'gray' => Color::Gray,
            //     'info' => Color::Blue,
            //     'primary' => Color::Indigo,
            //     'success' => Color::Emerald,
            //     'warning' => Color::Orange,
            // ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                //Widgets\FilamentInfoWidget::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $builder->groups([
                    NavigationGroup::make('Dashboard')
                        ->items([
                            NavigationItem::make('Dashboard')
                                ->icon('heroicon-o-home')
                                ->activeIcon('heroicon-s-home')
                                ->url('/admin'),
                        ]),
                ]);
                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin') {
                    $builder->groups([
                        NavigationGroup::make('Master')
                            ->items([
                                NavigationItem::make('MollakTenant')
                                    ->url('/admin/mollak-tenants')
                                    ->icon('heroicon-o-users')
                                    ->activeIcon('heroicon-o-users')
                                    ->sort(1),
                                NavigationItem::make('Medias')
                                    ->url('/admin/media')
                                    ->icon('heroicon-m-photo')
                                    ->activeIcon('heroicon-m-photo')
                                    ->sort(2),
                                NavigationItem::make('Owner Association')
                                    ->url('/admin/owner-associations')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                                    ->icon('heroicon-s-user-group')
                                    ->activeIcon('heroicon-s-user-group')
                                    ->sort(3),
                                NavigationItem::make('Cities')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                                    ->url('/admin/master/cities')
                                    ->icon('heroicon-m-globe-americas')
                                    ->activeIcon('heroicon-m-globe-americas')
                                    ->sort(4),
                                NavigationItem::make('Document Libraries')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->url('/admin/master/document-libraries')
                                    ->icon('heroicon-m-document-chart-bar')
                                    ->activeIcon('heroicon-m-document-chart-bar')
                                    ->sort(5),
                                NavigationItem::make('Facilities')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                                    ->url('/admin/master/facilities')
                                    ->icon('heroicon-o-cube-transparent')
                                    ->activeIcon('heroicon-o-cube-transparent')
                                    ->sort(6),
                                NavigationItem::make('Roles')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                                    ->url('/admin/master/roles')
                                    ->icon('heroicon-s-user-group')
                                    ->activeIcon('heroicon-s-user-group')
                                    ->sort(7),
                                NavigationItem::make('Services')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? false : true)
                                    ->url('/admin/master/services')
                                    ->icon('heroicon-m-wrench')
                                    ->activeIcon('heroicon-m-wrench')
                                    ->sort(8),
                            ]),
                    ]);
                }
                $builder->groups([
                    NavigationGroup::make('Community')
                        ->items([
                            NavigationItem::make('Announcements')
                                ->url('/admin/announcements')
                                ->icon('heroicon-o-megaphone')
                                ->activeIcon('heroicon-o-megaphone')
                                ->sort(1),
                            NavigationItem::make('Posts')
                                ->url('/admin/posts')
                                ->icon('heroicon-m-photo')
                                ->activeIcon('heroicon-m-photo')
                                ->sort(2),
                        ]),
                ]);
                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
                    $builder->groups([
                        NavigationGroup::make('User Management')
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
                $builder->groups([
                    NavigationGroup::make('Property Management')
                        ->items([
                            NavigationItem::make('Security')
                                ->url('/admin/building/building-pocs')
                                ->icon('heroicon-m-shield-exclamation')
                                ->activeIcon('heroicon-m-shield-exclamation')
                                ->sort(1),
                            NavigationItem::make('Buildings')
                                ->url('/admin/building/buildings')
                                ->icon('heroicon-m-clipboard-document-check')
                                ->activeIcon('heroicon-m-clipboard-document-check')
                                ->sort(2),
                            NavigationItem::make('Budget')
                                ->url('/admin/budgets')
                                ->icon('heroicon-m-clipboard-document-check')
                                ->activeIcon('heroicon-m-clipboard-document-check')
                                ->sort(3),
                            NavigationItem::make('Facility Bookings')
                                ->url('/admin/building/facility-bookings')
                                ->icon('heroicon-o-cube-transparent')
                                ->activeIcon('heroicon-o-cube-transparent')
                                ->sort(4),
                            NavigationItem::make('Service Bookings')
                                ->url('/admin/building/service-bookings')
                                ->icon('heroicon-m-wrench')
                                ->activeIcon('heroicon-m-wrench')
                                ->sort(5),
                            NavigationItem::make('Assets')
                                ->url('/admin/assets')
                                ->icon('heroicon-m-wrench')
                                ->activeIcon('heroicon-m-wrench')
                                ->sort(6),
                        ]),
                ]);
                $builder->groups([
                    NavigationGroup::make('Flat Management')
                        ->items([
                            NavigationItem::make('Flats')
                                ->url('/admin/building/flats')
                                ->icon('heroicon-o-home')
                                ->activeIcon('heroicon-o-home')
                                ->sort(1),
                            NavigationItem::make('Tenants')
                                ->url('/admin/building/flat-tenants')
                                ->icon('heroicon-o-user-circle')
                                ->activeIcon('heroicon-o-user-circle')
                                ->sort(2),
                        ]),
                ]);
                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
                    $builder->groups([
                        NavigationGroup::make('Document Management')
                            ->items([
                                NavigationItem::make('Buildings')
                                    ->url('/admin/building-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-o-building-office-2')
                                    ->activeIcon('heroicon-o-building-office-2')
                                    ->sort(1),
                                NavigationItem::make('Flats')
                                    ->url('/admin/flat-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-o-home')
                                    ->activeIcon('heroicon-o-home')
                                    ->sort(2),
                                NavigationItem::make('Residents')
                                    ->url('/admin/tenant-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-o-user-circle')
                                    ->activeIcon('heroicon-o-user-circle')
                                    ->sort(2),
                            ]),
                    ]);
                }
                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
                    $builder->groups([
                        NavigationGroup::make('Forms Document')
                            ->items([
                                NavigationItem::make('Guest Registration')
                                    ->url('/admin/guest-registrations')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-identification')
                                    ->activeIcon('heroicon-m-identification')
                                    ->sort(1),
                                NavigationItem::make('MoveIn')
                                    ->url('/admin/move-in-forms-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-arrow-right-circle')
                                    ->activeIcon('heroicon-s-arrow-right-circle')
                                    ->sort(2),
                                NavigationItem::make('MoveOut')
                                    ->url('/admin/move-out-forms-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-arrow-left-circle')
                                    ->activeIcon('heroicon-s-arrow-left-circle')
                                    ->sort(3),
                                NavigationItem::make('FitOut')
                                    ->url('/admin/fit-out-forms-documents')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-s-face-smile')
                                    ->activeIcon('heroicon-s-face-smile')
                                    ->sort(3),
                                NavigationItem::make('Access Card')
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
                            ]),
                    ]);
                }
                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
                    $builder->groups([
                        NavigationGroup::make('Vendor Management')
                            ->items([
                                NavigationItem::make('Vendor')
                                    ->url('/admin/vendor/vendors')
                                    ->hidden(DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] == 'Admin' ? true : false)
                                    ->icon('heroicon-m-user-circle')
                                    ->activeIcon('heroicon-m-user-circle')
                                    ->sort(1),
                            ]),
                    ]);
                }
                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
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
                if (DB::table('roles')->where('id', auth()->user()->role_id)->pluck('name')[0] != 'Admin') {
                    $builder->groups([
                        NavigationGroup::make('Help Desk')
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
                return $builder;
            })
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
