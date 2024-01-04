<?php

namespace App\Filament\Resources\SnagsResource\Pages;

use Filament\Actions;
use App\Filament\Resources\SnagsResource;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSnags extends ListRecords
{
    protected static string $resource = SnagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if(Role::where('id',auth()->user()->role_id)->first()->name == 'Admin') 
        {
            return parent::getTableQuery()->where('complaint_type', 'snag');
        }
        return parent::getTableQuery()->where('complaint_type', 'snag')->where('owner_association_id',auth()->user()->owner_association_id);
    }
}
