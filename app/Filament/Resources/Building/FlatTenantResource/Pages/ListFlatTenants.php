<?php

namespace App\Filament\Resources\Building\FlatTenantResource\Pages;

use App\Filament\Resources\Building\FlatTenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListFlatTenants extends ListRecords
{
    protected static string $resource = FlatTenantResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    protected function getTableQuery(): Builder
    {   
        if(auth()->user()->id == 1)
        {
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
    }
}
