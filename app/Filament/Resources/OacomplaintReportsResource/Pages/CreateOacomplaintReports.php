<?php

namespace App\Filament\Resources\OacomplaintReportsResource\Pages;

use App\Filament\Resources\OacomplaintReportsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOacomplaintReports extends CreateRecord
{
    protected static string $resource = OacomplaintReportsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
