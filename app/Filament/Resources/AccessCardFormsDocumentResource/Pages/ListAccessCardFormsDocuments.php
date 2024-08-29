<?php

namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AccessCardFormsDocumentResource;

class ListAccessCardFormsDocuments extends ListRecords
{
    protected static string $resource = AccessCardFormsDocumentResource::class;
    protected static ?string $title = 'Access card';
    protected function getTableQuery(): Builder
    {
        return auth()->user()->role->name == 'Admin' ? parent::getTableQuery() : parent::getTableQuery()->where('owner_association_id', auth()->user()?->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
