<?php

namespace App\Filament\Resources\HelpdeskcomplaintResource\Pages;

use App\Filament\Resources\HelpdeskcomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHelpdeskcomplaints extends ListRecords
{
    protected static string $resource = HelpdeskcomplaintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
