<?php

namespace App\Filament\Resources\LedgersResource\Pages;

use Filament\Actions;
use App\Models\Building\Building;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LedgersResource;

class ListLedgers extends ListRecords
{
    protected static string $resource = LedgersResource::class;
    protected static ?string $title = 'Service charge ledgers';

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->whereIn('building_id', Building::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'));
    }

}
