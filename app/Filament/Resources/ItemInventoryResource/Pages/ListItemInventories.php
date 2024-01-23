<?php

namespace App\Filament\Resources\ItemInventoryResource\Pages;

use App\Filament\Resources\ItemInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemInventories extends ListRecords
{
    protected static string $resource = ItemInventoryResource::class;
    protected static ?string $title = 'Item inventory';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
