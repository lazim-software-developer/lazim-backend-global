<?php

namespace App\Filament\Resources\EmailReminderResource\Pages;

use App\Filament\Resources\EmailReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailReminder extends ListRecords
{
    protected static string $resource = EmailReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
