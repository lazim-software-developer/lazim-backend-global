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
use App\Models\Building\Building;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Pages\Tenancy\RegisterBuilding;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Pages\Tenancy\EditBuildingProfile;
use App\Models\OaUserRegistration;
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
            // ->tenant(OaUserRegistration::class)
            // ->tenantRegistration(RegisterBuilding::class)
            //  ->tenantProfile(EditBuildingProfile::class)
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(EditProfile::class)
            ->colors([
                'primary' => Color::Amber,
            ])
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
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder
            {
            $builder->groups([
                NavigationGroup::make('Dashboard')
                    ->items([
                            NavigationItem::make('Dashboard')
                                ->icon('heroicon-o-home')
                                ->activeIcon('heroicon-s-home')
                                ->url('/admin'),
                            ]),
            ]);
           if(auth()->user()->id == 1)
           {
            $builder->groups([
                NavigationGroup::make('Master')
                    ->items([
                            NavigationItem::make('MollakTenant')
                                ->url('/admin/mollak-tenants')
                                ->icon('heroicon-o-calendar-days')
                                ->activeIcon('heroicon-o-calendar-days')
                                ->sort(1),
                            NavigationItem::make('Medias')
                                ->url('/admin/media')
                                ->icon('heroicon-o-calendar-days')
                                ->activeIcon('heroicon-o-calendar-days')
                                ->sort(2),
                            NavigationItem::make('Owner Association')
                                ->url('/admin/owner-associations')
                                ->hidden(auth()->user()->id == 1 ? false : true)
                                ->icon('heroicon-o-calendar-days')
                                ->activeIcon('heroicon-o-calendar-days')
                                ->sort(3),
                            NavigationItem::make('Cities')
                                ->hidden(auth()->user()->id == 1 ? false : true)
                                ->url('/admin/master/cities')
                                ->icon('heroicon-o-calendar-days')
                                ->activeIcon('heroicon-o-calendar-days')
                                ->sort(4),
                            NavigationItem::make('Document Libraries')
                                ->hidden(auth()->user()->id == 1 ? true : false)
                                ->url('/admin/master/document-libraries')
                                ->icon('heroicon-m-clipboard-document-check')
                                ->activeIcon('heroicon-m-clipboard-document-check')
                                ->sort(5),
                            NavigationItem::make('Facilities')
                                ->hidden(auth()->user()->id == 1 ? false : true)
                                ->url('/admin/master/facilities')
                                ->icon('heroicon-s-speaker-wave')
                                ->activeIcon('heroicon-s-speaker-wave')
                                ->sort(6),
                            NavigationItem::make('Roles')
                                ->hidden(auth()->user()->id == 1 ? false : true)
                                ->url('/admin/master/roles')
                                ->icon('heroicon-s-user-group')
                                ->activeIcon('heroicon-s-user-group')
                                ->sort(7),
                            NavigationItem::make('Services')
                                ->hidden(auth()->user()->id == 1 ? false : true)
                                ->url('/admin/master/services')
                                ->icon('heroicon-s-user-group')
                                ->activeIcon('heroicon-s-user-group')
                                ->sort(8),
                            ]),
            ]);
           }
            $builder->groups([
                NavigationGroup::make('Community')
                    ->items([
                            NavigationItem::make('Announcements')
                                ->url('/admin/announcements')
                                ->icon('heroicon-o-calendar-days')
                                ->activeIcon('heroicon-o-calendar-days')
                                ->sort(1),
                            NavigationItem::make('Posts')
                                ->url('/admin/posts')
                                ->icon('heroicon-m-clipboard-document-check')
                                ->activeIcon('heroicon-m-clipboard-document-check')
                                ->sort(2),
                            ]),
            ]);
            $builder->groups([
                NavigationGroup::make('Property Management')
                    ->items([
                            NavigationItem::make('Building Managers')
                                ->url('/admin/building/building-pocs')
                                ->icon('heroicon-o-calendar-days')
                                ->activeIcon('heroicon-o-calendar-days')
                                ->sort(1),
                            NavigationItem::make('Buildings')
                                ->url('/admin/building/buildings')
                                ->icon('heroicon-m-clipboard-document-check')
                                ->activeIcon('heroicon-m-clipboard-document-check')
                                ->sort(2),
                            NavigationItem::make('Facility Bookings')
                                ->url('/admin/building/facility-bookings')
                                ->icon('heroicon-s-speaker-wave')
                                ->activeIcon('heroicon-s-speaker-wave')
                                ->sort(3),
                            NavigationItem::make('Service Bookings')
                                ->url('/admin/building/service-bookings')
                                ->icon('heroicon-s-user-group')
                                ->activeIcon('heroicon-s-user-group')
                                ->sort(4),
                            ]),
            ]);
            $builder->groups([
                NavigationGroup::make('Flat Management')
                    ->items([
                            NavigationItem::make('Flats')
                                ->url('/admin/building/flats')
                                ->icon('heroicon-o-information-circle')
                                ->activeIcon('heroicon-o-information-circle')
                                ->sort(1),
                            NavigationItem::make('Tenants')
                                ->url('/admin/building/flat-tenants')
                                ->icon('heroicon-o-user-circle')
                                ->activeIcon('heroicon-o-user-circle')
                                ->sort(2),
                            ]),
            ]);
            if(auth()->user()->id != 1)
            {
                $builder->groups([
                    NavigationGroup::make('Document Management')
                        ->items([
                                NavigationItem::make('Buildings')
                                    ->url('/admin/building-documents')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-calendar-days')
                                    ->activeIcon('heroicon-o-calendar-days')
                                    ->sort(1),
                                NavigationItem::make('Flats')
                                    ->url('/admin/flat-documents')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(2),
                                NavigationItem::make('Tenant')
                                    ->url('/admin/tenant-documents')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-user-circle')
                                    ->activeIcon('heroicon-o-user-circle')
                                    ->sort(2),
                                ]),
                ]);
            }
            if(auth()->user()->id != 1)
            {
                $builder->groups([
                    NavigationGroup::make('Forms Document')
                        ->items([
                                NavigationItem::make('Guest Registration')
                                    ->url('/admin/guest-registrations')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-calendar-days')
                                    ->activeIcon('heroicon-o-calendar-days')
                                    ->sort(1),
                                NavigationItem::make('Move-IN')
                                    ->url('/admin/move-in-forms-documents')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-calendar-days')
                                    ->activeIcon('heroicon-o-calendar-days')
                                    ->sort(2),
                                NavigationItem::make('Move-Out')
                                    ->url('/admin/move-out-forms-documents')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-calendar-days')
                                    ->activeIcon('heroicon-o-calendar-days')
                                    ->sort(3),
                                ]),
                ]);
            }
            if(auth()->user()->id != 1)
            {
                $builder->groups([
                    NavigationGroup::make('Vendor Management')
                        ->items([
                                NavigationItem::make('Vendor')
                                    ->url('/admin/vendor/vendors')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-calendar-days')
                                    ->activeIcon('heroicon-o-calendar-days')
                                    ->sort(1),
                                ]),
                ]);
            }
            if(auth()->user()->id != 1)
            {
                $builder->groups([
                    NavigationGroup::make('Happiness center')
                        ->items([
                                NavigationItem::make('Complaints')
                                    ->url('/admin/complaintscomplaints')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-calendar-days')
                                    ->activeIcon('heroicon-o-calendar-days')
                                    ->sort(1),
                                NavigationItem::make('Enquirys')
                                    ->url('/admin/complaintsenquiries')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(2),
                                NavigationItem::make('Suggestions')
                                    ->url('/admin/complaintssuggessions')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-m-clipboard-document-check')
                                    ->activeIcon('heroicon-m-clipboard-document-check')
                                    ->sort(3),
                                ]),
                ]);
            }
            if(auth()->user()->id != 1)
            {
                $builder->groups([
                    NavigationGroup::make('Help Desk')
                        ->items([
                                NavigationItem::make('Complaints')
                                    ->url('/admin/helpdeskcomplaints')
                                    ->hidden(auth()->user()->id == 1 ? true : false)
                                    ->icon('heroicon-o-calendar-days')
                                    ->activeIcon('heroicon-o-calendar-days')
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
