<?php

namespace App\Filament\Resources\VendorLedgersResource\Pages;

use App\Filament\Resources\VendorLedgersResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVendorLedgers extends ViewRecord
{
    protected static string $resource = VendorLedgersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
}
