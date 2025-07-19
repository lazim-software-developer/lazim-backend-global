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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            //Actions\DeleteAction::make(),
        ];
    }
}
