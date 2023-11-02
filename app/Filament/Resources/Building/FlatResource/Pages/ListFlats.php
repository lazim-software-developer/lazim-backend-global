<?php

namespace App\Filament\Resources\Building\FlatResource\Pages;

use App\Filament\Resources\Building\FlatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListFlats extends ListRecords
{
    protected static string $resource = FlatResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('owner_association_id',auth()->user()->owner_association_id);
    }
}
