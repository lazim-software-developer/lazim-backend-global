<?php

namespace App\Filament\Resources\SendBulkEmailResource\Pages;

use App\Filament\Resources\SendBulkEmailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSendBulkEmails extends ListRecords
{
    protected static string $resource = SendBulkEmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
