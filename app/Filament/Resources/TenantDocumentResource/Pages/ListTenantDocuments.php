<?php

namespace App\Filament\Resources\TenantDocumentResource\Pages;

use App\Filament\Resources\TenantDocumentResource;
use App\Models\Master\Role;
use DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTenantDocuments extends ListRecords
{
    protected static string $resource = TenantDocumentResource::class;
    protected function getTableQuery(): Builder
    {
        $pmBuildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        if (auth()->user()->role->name == 'Admin') {
            return parent::getTableQuery();
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
            return parent::getTableQuery()->whereIn('building_id', $pmBuildings);
        }

        return parent::getTableQuery()->whereIn('building_id', $pmBuildings);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
