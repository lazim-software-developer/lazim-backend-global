<?php

namespace App\Filament\Resources\NotificationListResource\Pages;

use Filament\Actions;
use App\Models\NotificationHistory;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\NotificationListResource;

class ViewNotificationSent extends ViewRecord
{
    protected static string $resource = NotificationListResource::class;

    // protected function getHeaderActions(): array
    // {
    //     // return [
    //     //     backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition

    //     // ];
    // }
}
