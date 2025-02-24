<?php

namespace App\Filament\Resources\AgingReportResource\Pages;

use App\Filament\Resources\AgingReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAgingReport extends EditRecord
{
    protected static string $resource = AgingReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
