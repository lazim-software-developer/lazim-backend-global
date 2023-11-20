<?php

namespace App\Filament\Resources\TechniciansResource\Pages;

use App\Filament\Resources\TechniciansResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTechnicians extends ListRecords
{
    protected static string $resource = TechniciansResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
