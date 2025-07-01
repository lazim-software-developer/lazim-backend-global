<?php

namespace App\Filament\Resources\TenderResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TenderResource;
use App\Models\Master\Role;

class ListTenders extends ListRecords
{
    protected static string $resource = TenderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            //Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('building_id', Building::where('owner_association_id', auth()->user()?->owner_association_id)->pluck('id'));
    }
}
