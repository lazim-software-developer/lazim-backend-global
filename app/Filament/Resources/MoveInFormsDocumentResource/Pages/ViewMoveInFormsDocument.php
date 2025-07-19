<?php

namespace App\Filament\Resources\MoveInFormsDocumentResource\Pages;

use App\Filament\Resources\MoveInFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMoveInFormsDocument extends ViewRecord
{
    protected static string $resource = MoveInFormsDocumentResource::class;
    protected static ?string $title = 'MoveIn';

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
