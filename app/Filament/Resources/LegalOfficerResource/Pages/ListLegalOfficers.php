<?php

namespace App\Filament\Resources\LegalOfficerResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LegalOfficerResource;

class ListLegalOfficers extends ListRecords
{
    protected static string $resource = LegalOfficerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where(['owner_association_id' => auth()->user()->owner_association_id, 'role_id' => Role::where('name', 'Legal Officer')->first()->id]);
    }
}
