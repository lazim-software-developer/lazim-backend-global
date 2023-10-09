<?php

namespace App\Filament\Resources\OaUserRegistrationResource\Pages;

use App\Filament\Resources\OaUserRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOaUserRegistrations extends ListRecords
{
    protected static string $resource = OaUserRegistrationResource::class;
    protected ?string $heading        = 'OA User';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
