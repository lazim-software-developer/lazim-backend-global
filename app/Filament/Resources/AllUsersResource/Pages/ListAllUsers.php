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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
}
