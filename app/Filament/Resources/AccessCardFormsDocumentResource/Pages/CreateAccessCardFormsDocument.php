<?php

namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAccessCardFormsDocument extends CreateRecord
{
    protected static string $resource = AccessCardFormsDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
