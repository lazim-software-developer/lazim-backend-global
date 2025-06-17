<?php

namespace App\Filament\Resources\PollResource\Pages;

use App\Models\OwnerAssociation;
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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if(auth()->user()->role->name == 'Admin'){
            return parent::getTableQuery();
        }
        if(auth()->user()->role->name == 'Property Manager'
        || OwnerAssociation::where('id', auth()->user()?->owner_association_id)
                ->pluck('role')[0] == 'Property Manager')
        {
            return parent::getTableQuery()->where('owner_association_id',auth()->user()?->owner_association_id);
        }
        if(Role::where('id',auth()->user()->role_id)->first()->name != 'Admin')
        {
            // return parent::getTableQuery()->whereIn('building_id',Building::where('owner_association_id',auth()->user()?->owner_association_id)->pluck('id'));
            return Poll::where('owner_association_id',Filament::getTenant()->id);
        }
        return parent::getTableQuery();
    }
}
