<?php

namespace App\Filament\Resources\NotificationListResource\Pages;

use App\Filament\Resources\NotificationListResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotificationLists extends ListRecords
{
    protected static string $resource = NotificationListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
}
