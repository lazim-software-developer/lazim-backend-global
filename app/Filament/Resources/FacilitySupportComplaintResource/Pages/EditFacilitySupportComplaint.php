<?php

namespace App\Filament\Resources\FacilitySupportComplaintResource\Pages;

use App\Filament\Resources\FacilitySupportComplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacilitySupportComplaint extends EditRecord
{
    protected static string $resource = FacilitySupportComplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
