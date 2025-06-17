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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            Actions\DeleteAction::make(),
        ];
    }
}
