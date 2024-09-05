<?php

namespace App\Filament\Resources\LegalNoticeResource\Pages;

use App\Filament\Resources\LegalNoticeResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLegalNotice extends ViewRecord
{
    protected static string $resource = LegalNoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
