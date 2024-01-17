<?php

namespace App\Filament\Resources\ItemInventoryResource\Pages;

use App\Filament\Resources\ItemInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewItemInventory extends ViewRecord
{
    protected static string $resource = ItemInventoryResource::class;
    protected static ?string $title = 'Item inventory';
}
