<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use App\Filament\Resources\User\OwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOwners extends ListRecords
{
    protected static string $resource = OwnerResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('role_id',1);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
