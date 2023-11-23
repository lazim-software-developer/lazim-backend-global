<?php

namespace App\Filament\Resources\Master\VendorServiceResource\Pages;

use App\Filament\Resources\Master\VendorServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorService extends EditRecord
{
    protected static string $resource = VendorServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
