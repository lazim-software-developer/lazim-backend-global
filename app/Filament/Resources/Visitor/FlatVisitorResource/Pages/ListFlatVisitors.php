<?php

namespace App\Filament\Resources\Visitor\FlatVisitorResource\Pages;

use App\Filament\Resources\Visitor\FlatVisitorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFlatVisitors extends ListRecords
{
    protected static string $resource = FlatVisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
