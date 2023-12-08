<?php

namespace App\Filament\Resources\Building\ServiceBookingResource\Pages;

use App\Filament\Resources\Building\ServiceBookingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceBooking extends EditRecord
{
    protected static string $resource = ServiceBookingResource::class;

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
