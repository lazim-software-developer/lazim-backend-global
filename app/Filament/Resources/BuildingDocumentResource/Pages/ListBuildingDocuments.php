<?php

namespace App\Filament\Resources\BuildingDocumentResource\Pages;

use App\Filament\Resources\BuildingDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBuildingDocuments extends ListRecords
{
    protected static string $resource = BuildingDocumentResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
