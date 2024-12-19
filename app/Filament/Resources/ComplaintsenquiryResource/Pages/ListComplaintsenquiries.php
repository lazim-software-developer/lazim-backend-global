<?php

namespace App\Filament\Resources\ComplaintsenquiryResource\Pages;

use App\Filament\Resources\ComplaintsenquiryResource;
use App\Models\Master\Role;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListComplaintsenquiries extends ListRecords
{
    protected static string $resource = ComplaintsenquiryResource::class;
    protected function getTableQuery(): Builder
    {
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery()->where('complaint_type', 'enquiries');
        }
        return parent::getTableQuery()->where('complaint_type', 'enquiries')->where('owner_association_id', auth()->user()?->owner_association_id);
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
