<?php

namespace App\Filament\Resources\PostResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;
    protected function getTableQuery(): Builder
    {
        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin') 
        {
            return parent::getTableQuery()->where('is_announcement',0)->where('owner_association_id',auth()->user()->owner_association_id);
        }
        return parent::getTableQuery()->where('is_announcement',0);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
