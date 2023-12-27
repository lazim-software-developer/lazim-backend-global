<?php

namespace App\Filament\Resources\VisitorFormResource\Pages;

use App\Filament\Resources\VisitorFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
}
