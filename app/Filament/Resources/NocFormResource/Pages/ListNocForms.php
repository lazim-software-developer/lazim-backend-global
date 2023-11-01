<?php

namespace App\Filament\Resources\NocFormResource\Pages;

use App\Filament\Resources\NocFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNocForms extends ListRecords
{
    protected static string $resource = NocFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
