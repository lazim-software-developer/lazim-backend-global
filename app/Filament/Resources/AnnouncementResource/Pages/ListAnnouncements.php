<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AnnouncementResource;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;
    protected static ?string $title = 'Notice board';
    protected static ?string $modeLabel = 'Notice board';
    protected function getTableQuery(): Builder
    {
        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin') 
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
