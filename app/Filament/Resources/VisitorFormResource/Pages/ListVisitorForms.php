<?php

namespace App\Filament\Resources\VisitorFormResource\Pages;

use App\Filament\Resources\VisitorFormResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListVisitorForms extends ListRecords
{
    protected static string $resource = VisitorFormResource::class;
    protected static ?string $title   = 'Flat visitors';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return auth()->user()->role->name == 'Admin' ? parent::getTableQuery():
        parent::getTableQuery()->where('owner_association_id', auth()->user()?->owner_association_id);
    }
}
