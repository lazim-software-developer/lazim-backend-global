<?php

namespace App\Filament\Resources\OacomplaintReportsResource\Pages;

use App\Filament\Resources\OacomplaintReportsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOacomplaintReports extends ListRecords
{
    protected static string $resource = OacomplaintReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
}
