<?php

namespace App\Filament\Resources\FitOutFormsDocumentResource\Pages;

use App\Filament\Resources\FitOutFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFitOutFormsDocuments extends ListRecords
{
    protected static string $resource = FitOutFormsDocumentResource::class;
    protected static ?string $title = 'FitOut';
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
