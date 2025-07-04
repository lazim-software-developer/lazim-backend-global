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
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
        ];
    }
}
