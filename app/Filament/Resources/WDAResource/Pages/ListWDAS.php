<?php

namespace App\Filament\Resources\WDAResource\Pages;

use Filament\Actions;
use App\Filament\Resources\WDAResource;
use App\Models\Vendor\Vendor;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListWDAS extends ListRecords
{
    protected static ?string $title = 'WDA';
    protected static string $resource = WDAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->whereIn('vendor_id',Vendor::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id'));
    }
}
