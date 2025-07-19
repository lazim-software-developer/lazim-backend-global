<?php

namespace App\Filament\Resources\SubContractorResource\Pages;

use App\Filament\Resources\SubContractorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubContractor extends EditRecord
{
    protected static string $resource = SubContractorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
