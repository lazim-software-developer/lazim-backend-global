<?php

namespace App\Filament\Resources\ItemInventoryResource\Pages;

use App\Filament\Resources\ItemInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateItemInventory extends CreateRecord
{
    protected static string $resource = ItemInventoryResource::class;
    protected static ?string $title = 'Item inventorys';
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
