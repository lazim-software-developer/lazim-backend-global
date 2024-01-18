<?php

namespace App\Filament\Resources\AgingReportResource\Pages;

use App\Filament\Resources\AgingReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAgingReports extends ListRecords
{
    protected static string $resource = AgingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
