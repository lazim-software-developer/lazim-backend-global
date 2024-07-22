<?php

namespace App\Filament\Resources\IncidentResource\Pages;

use App\Filament\Resources\IncidentResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListIncidents extends ListRecords
{
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('type','incident');
    }
}
