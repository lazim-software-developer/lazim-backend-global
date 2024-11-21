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
        return parent::getTableQuery()->where('complaint_type', 'tenant_complaint');
        }
        
        return parent::getTableQuery()->where('complaint_type', 'tenant_complaint')->where('owner_association_id',auth()->user()?->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
