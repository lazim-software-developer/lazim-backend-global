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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
}
