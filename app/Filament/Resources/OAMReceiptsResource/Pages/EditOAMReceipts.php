<?php

namespace App\Filament\Resources\OAMReceiptsResource\Pages;

use App\Filament\Resources\OAMReceiptsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOAMReceipts extends EditRecord
{
    protected static string $resource = OAMReceiptsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
