<?php

namespace App\Filament\Resources\GuestRegistrationResource\Pages;

use App\Filament\Resources\GuestRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGuestRegistrations extends ListRecords
{
    protected static string $resource = GuestRegistrationResource::class;
    protected static ?string $title = 'Guests';
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
