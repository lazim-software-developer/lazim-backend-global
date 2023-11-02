<?php

namespace App\Filament\Resources\FlatDocumentResource\Pages;

use App\Filament\Resources\FlatDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFlatDocuments extends ListRecords
{
    protected static string $resource = FlatDocumentResource::class;
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
