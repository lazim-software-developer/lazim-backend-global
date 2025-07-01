<?php

namespace App\Filament\Resources\NotificationListResource\Pages;

use App\Filament\Resources\NotificationListResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationList extends EditRecord
{
    protected static string $resource = NotificationListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
