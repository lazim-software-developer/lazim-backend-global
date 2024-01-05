<?php

namespace App\Filament\Resources\SnagsResource\Pages;

use App\Filament\Resources\SnagsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSnags extends EditRecord
{
    protected static string $resource = SnagsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
