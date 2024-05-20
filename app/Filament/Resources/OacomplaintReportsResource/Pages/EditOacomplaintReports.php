<?php

namespace App\Filament\Resources\OacomplaintReportsResource\Pages;

use App\Filament\Resources\OacomplaintReportsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOacomplaintReports extends EditRecord
{
    protected static string $resource = OacomplaintReportsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
