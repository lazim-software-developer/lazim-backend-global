<?php

namespace App\Filament\Resources\GuestRegistrationResource\Pages;

use App\Filament\Resources\GuestRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewGuestRegistration extends ViewRecord
{
    protected static string $resource = GuestRegistrationResource::class;
    protected static ?string $title = 'Guest Registration';
}
