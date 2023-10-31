<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\Pages;

use App\Filament\Resources\ComplaintscomplaintResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaintscomplaints extends ListRecords
{
    protected static string $resource = ComplaintscomplaintResource::class;
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('complaint_type', 'tenant_complaint');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
