<?php

namespace App\Filament\Resources\MoveOutFormsDocumentResource\Pages;

use App\Filament\Resources\MoveOutFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMoveOutFormsDocument extends ViewRecord
{
    protected static string $resource = MoveOutFormsDocumentResource::class;
    protected static ?string $title = 'MoveOut';

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
