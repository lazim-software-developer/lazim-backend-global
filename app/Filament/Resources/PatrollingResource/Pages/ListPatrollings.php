<?php

namespace App\Filament\Resources\PatrollingResource\Pages;

use App\Filament\Resources\PatrollingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatrollings extends ListRecords
{
    protected static string $resource = PatrollingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
