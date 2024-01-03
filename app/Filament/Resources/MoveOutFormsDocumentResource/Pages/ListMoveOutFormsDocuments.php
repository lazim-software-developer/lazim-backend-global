<?php

namespace App\Filament\Resources\MoveOutFormsDocumentResource\Pages;

use App\Filament\Resources\MoveOutFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMoveOutFormsDocuments extends ListRecords
{
    protected static string $resource = MoveOutFormsDocumentResource::class;
    protected static ?string $title = 'Move out';
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
