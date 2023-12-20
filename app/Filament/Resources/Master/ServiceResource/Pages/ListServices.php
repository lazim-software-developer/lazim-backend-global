<?php

namespace App\Filament\Resources\Master\ServiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Master\ServiceResource;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('type','inhouse');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
