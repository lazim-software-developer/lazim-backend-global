<?php

namespace App\Filament\Resources\NocFormResource\Pages;

use App\Filament\Resources\NocFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNocForms extends ListRecords
{
    protected static string $resource = NocFormResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
