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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
}
