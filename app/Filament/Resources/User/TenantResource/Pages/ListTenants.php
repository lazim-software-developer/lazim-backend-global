<?php

namespace App\Filament\Resources\User\TenantResource\Pages;

use App\Filament\Resources\User\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;
    // protected function getTableQuery(): Builder
    // {
    //     return parent::getTableQuery()->where('role_id',11)->where('owner_association_id',auth()->user()->owner_association_id);
    // }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
