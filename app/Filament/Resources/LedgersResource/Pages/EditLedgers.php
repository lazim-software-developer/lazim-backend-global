<?php

namespace App\Filament\Resources\LedgersResource\Pages;

use App\Filament\Resources\LedgersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLedgers extends EditRecord
{
    protected static string $resource = LedgersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
