<?php

namespace App\Filament\Resources\VisitorFormResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\VisitorFormResource;
use App\Models\Building\Building;

class ListVisitorForms extends ListRecords
{
    protected static string $resource = VisitorFormResource::class;
    protected static ?string $title = 'Flat visitors';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->whereIn('building_id',Building::where('owner_association_id',auth()->user()->owner_association_id)->pluck('id'));
    }
}
