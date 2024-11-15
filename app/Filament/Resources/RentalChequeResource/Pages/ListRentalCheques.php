<?php

namespace App\Filament\Resources\RentalChequeResource\Pages;

use App\Filament\Resources\RentalChequeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRentalCheques extends ListRecords
{
    protected static string $resource = RentalChequeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
