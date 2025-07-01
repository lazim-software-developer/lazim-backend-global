<?php

namespace App\Filament\Resources\OAMReceiptsResource\Pages;

use App\Filament\Resources\OAMReceiptsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOAMReceipts extends ViewRecord
{
    protected static string $resource = OAMReceiptsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\EditAction::make(),
        ];
    }
}
