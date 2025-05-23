<?php

namespace App\Filament\Resources\AllUsersResource\Pages;

use App\Filament\Resources\AllUsersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAllUsers extends ListRecords
{
    protected static string $resource = AllUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
