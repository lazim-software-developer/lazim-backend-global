<?php

namespace App\Filament\Resources\DelinquentOwnerResource\Pages;

use App\Filament\Resources\DelinquentOwnerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDelinquentOwner extends EditRecord
{
    protected static string $resource = DelinquentOwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
