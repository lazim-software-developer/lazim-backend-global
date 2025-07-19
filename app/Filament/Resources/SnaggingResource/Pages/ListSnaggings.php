<?php

namespace App\Filament\Resources\SnaggingResource\Pages;

use App\Filament\Resources\SnaggingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSnaggings extends ListRecords
{
    protected static string $resource = SnaggingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
