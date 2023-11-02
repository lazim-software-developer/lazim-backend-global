<?php

namespace App\Filament\Resources\ResidentialFormResource\Pages;

use App\Filament\Resources\ResidentialFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResidentialForms extends ListRecords
{
    protected static string $resource = ResidentialFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
