<?php

namespace App\Filament\Resources\Master\RoleResource\Pages;

use App\Filament\Resources\Master\RoleResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
    protected function afterCreate(){
        $tenant=Filament::getTenant();
        Role::where('id', $this->record->id)
            ->update([
                'building_id'=>$tenant->first()->id
            ]);

    }
}
