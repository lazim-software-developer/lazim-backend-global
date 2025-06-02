<?php

namespace App\Filament\Resources\ComplaintscomplaintResource\Pages;

use App\Filament\Resources\ComplaintscomplaintResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaintscomplaints extends ListRecords
{
    protected static string $resource = ComplaintscomplaintResource::class;
    protected function getTableQuery(): Builder
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
        return parent::getTableQuery();
        }

        return parent::getTableQuery()->whereNotIn('complaint_type', ['enquiries','suggestions'])->where('owner_association_id',auth()->user()?->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
