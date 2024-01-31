<?php

namespace App\Filament\Resources\ComplaintOfficerResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ComplaintOfficerResource;

class ListComplaintOfficers extends ListRecords
{
    protected static string $resource = ComplaintOfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where(['owner_association_id' => auth()->user()->owner_association_id, 'role_id' => Role::where('name', 'Complaint Officer')->first()->id]);
    }
}
