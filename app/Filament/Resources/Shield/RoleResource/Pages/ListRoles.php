<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use App\Filament\Resources\Shield\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
        //     return parent::getTableQuery();
        // }
        return parent::getTableQuery()->whereNotIn('name', ['Admin', 'Technician', 'Security', 'Tenant', 'OA', 'Owner', 'Managing Director', 'Vendor'])->where('owner_association_id', auth()->user()->owner_association_id);
    }
}
