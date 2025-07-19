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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }
}
