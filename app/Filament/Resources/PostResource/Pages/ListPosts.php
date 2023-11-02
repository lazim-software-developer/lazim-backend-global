<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;
    protected function getTableQuery(): Builder
    {
        if(auth()->user()->id != 1) 
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
