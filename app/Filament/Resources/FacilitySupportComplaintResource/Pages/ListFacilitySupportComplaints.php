<?php

namespace App\Filament\Resources\FacilitySupportComplaintResource\Pages;

use App\Filament\Resources\FacilitySupportComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFacilitySupportComplaints extends ListRecords
{
    protected static string $resource = FacilitySupportComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
