<?php

namespace App\Filament\Resources\ComplaintssuggessionResource\Pages;

use App\Filament\Resources\ComplaintssuggessionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaintssuggessions extends ListRecords
{
    protected static string $resource = ComplaintssuggessionResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('complaint_type', 'suggestions');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
