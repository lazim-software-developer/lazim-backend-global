<?php

namespace App\Filament\Resources\PollResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Models\Building\Building;
use App\Filament\Resources\PollResource;
use App\Models\Community\Poll;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPolls extends ListRecords
{
    protected static string $resource = PollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin') 
        {   
            // return parent::getTableQuery()->whereIn('building_id',Building::where('owner_association_id',auth()->user()?->owner_association_id)->pluck('id'));
            return Poll::where('owner_association_id',Filament::getTenant()->id);
        }
        return parent::getTableQuery();
    }
}
