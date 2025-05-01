<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNotificationSents extends ListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getTableQuery(): Builder
    {
        $query = static::getModel()::query()
            ->select('notifications.id', 'notifications.type', 'notifications.data', 'notifications.read_at', 'notifications.created_at')
            ->addSelect('custom_json_data->building_id as building_id')
            ->addSelect('custom_json_data->user_id as user_id')
            ->addSelect('custom_json_data->type as service_type')
            ->addSelect('data->title as title')
            ->addSelect('data->actions[0]->url as full_url')
            ->where('notifiable_id', '=', auth()->user()->id);

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }


}
