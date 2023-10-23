<?php

namespace App\Filament\Resources\HelpdeskcomplaintResource\Pages;

use App\Filament\Resources\HelpdeskcomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListHelpdeskcomplaints extends ListRecords
{
    protected static string $resource = HelpdeskcomplaintResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('complaint_type', 'help_desk');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
