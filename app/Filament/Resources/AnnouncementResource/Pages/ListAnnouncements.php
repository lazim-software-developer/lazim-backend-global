<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use App\Filament\Resources\AnnouncementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;
    protected static ?string $title = 'Notice board';
    protected static ?string $modeLabel = 'Notice board';
    protected function getTableQuery(): Builder
    {
        if(auth()->user()->id != 1) 
        {
            return parent::getTableQuery()->where('is_announcement',1)->where('owner_association_id',auth()->user()->owner_association_id);
        }
        return parent::getTableQuery()->where('is_announcement',1);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
