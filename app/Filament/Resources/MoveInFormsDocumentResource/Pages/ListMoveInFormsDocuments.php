<?php

namespace App\Filament\Resources\MoveInFormsDocumentResource\Pages;

use App\Filament\Resources\MoveInFormsDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMoveInFormsDocuments extends ListRecords
{
    protected static string $resource = MoveInFormsDocumentResource::class;
    protected static ?string $title = 'Move in';
    protected static ?string $modeLabel = 'Move in';
    protected function getTableQuery(): Builder
    {
        return auth()->user()->role->name == 'Admin' ? parent::getTableQuery() : parent::getTableQuery()->where('owner_association_id', auth()->user()?->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            //Actions\CreateAction::make(),
        ];
    }
}
