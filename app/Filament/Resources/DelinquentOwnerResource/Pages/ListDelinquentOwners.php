<?php

namespace App\Filament\Resources\DelinquentOwnerResource\Pages;

use App\Filament\Resources\DelinquentOwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDelinquentOwners extends ListRecords
{
    protected static string $resource = DelinquentOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
