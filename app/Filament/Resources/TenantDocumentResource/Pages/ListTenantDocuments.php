<?php

namespace App\Filament\Resources\TenantDocumentResource\Pages;

use App\Filament\Resources\TenantDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTenantDocuments extends ListRecords
{
    protected static string $resource = TenantDocumentResource::class;
    protected function getTableQuery(): Builder
    {
        if (auth()->user()->role->name == 'Admin') {
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->where('owner_association_id', auth()->user()?->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            //Actions\CreateAction::make(),
        ];
    }
}
