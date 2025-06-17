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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
        ];
    }
}
