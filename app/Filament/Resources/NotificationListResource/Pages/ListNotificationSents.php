<?php

namespace App\Filament\Resources\NotificationListResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\NotificationListResource;

class ListNotificationSents extends ListRecords
{
    protected static string $resource = NotificationListResource::class;

    protected function getHeaderActions(): array
    {

        return [
            backButton(url: url()->previous())->visible(), // TODO: Change this to the correct association ID or condition
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = static::getModel()::query()
            ->select('notifications.id', 'notifications.type', 'notifications.data', 'notifications.notifiable_type',  'notifications.notifiable_id', 'notifications.read_at', 'notifications.created_at')
            ->addSelect('data->title as title')
            ->addSelect('data->building as building')
            ->addSelect('data->actions[0]->url as full_url')
            ->where('notifiable_id', '=', auth()->user()->id);

        return $query;
    }
}
