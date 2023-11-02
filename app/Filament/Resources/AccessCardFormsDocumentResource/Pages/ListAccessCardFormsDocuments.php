<?php

namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAccessCardFormsDocuments extends ListRecords
{
    protected static string $resource = AccessCardFormsDocumentResource::class;
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
