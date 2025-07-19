<?php

namespace App\Filament\Resources\SendBulkEmailResource\Pages;

use App\Filament\Resources\SendBulkEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSendBulkEmail extends EditRecord
{
    protected static string $resource = SendBulkEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
