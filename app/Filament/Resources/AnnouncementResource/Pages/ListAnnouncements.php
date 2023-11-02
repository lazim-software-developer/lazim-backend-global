<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('is_announcement',1)->where('owner_association_id',auth()->user()->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
