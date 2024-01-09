<?php

namespace App\Filament\Resources\ItemInventoryResource\Pages;

use App\Filament\Resources\ItemInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemInventory extends EditRecord
{
    protected static string $resource = ItemInventoryResource::class;
    protected static ?string $title = 'Item inventorys';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
