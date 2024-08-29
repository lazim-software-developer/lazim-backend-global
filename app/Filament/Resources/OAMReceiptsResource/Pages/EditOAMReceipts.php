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
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
