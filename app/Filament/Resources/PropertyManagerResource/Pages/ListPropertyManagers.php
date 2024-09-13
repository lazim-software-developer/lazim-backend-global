<?php

namespace App\Filament\Resources\PropertyManagerResource\Pages;

use App\Filament\Resources\PropertyManagerResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPropertyManagers extends ListRecords
{
    protected static string $resource = PropertyManagerResource::class;

    protected ?string $heading = 'Property Management';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
     protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            return parent::getTableQuery()->where('role','Property Manager');
        }
        return parent::getTableQuery()->where('id', auth()->user()?->owner_association_id)->where('role','Property Manager');
    }
}
