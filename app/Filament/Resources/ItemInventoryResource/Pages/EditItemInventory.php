<?php

namespace App\Filament\Resources\ItemInventoryResource\Pages;

use App\Models\Item;
use Filament\Actions;
use App\Models\ItemInventory;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ItemInventoryResource;

class EditItemInventory extends EditRecord
{
    protected static string $resource = ItemInventoryResource::class;
    protected static ?string $title = 'Item inventory';

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function afterSave(): void
    {
        $record = $this->record;
        $item = Item::find($record->item_id);
        if ($record->type == 'incoming') {
            $item->quantity = $item->quantity + $record->quantity;
            $item->save();
        }
        if ($record->type == 'used') {
            $item->quantity = $item->quantity - $record->quantity;
            $item->save();
        }
    }
}
