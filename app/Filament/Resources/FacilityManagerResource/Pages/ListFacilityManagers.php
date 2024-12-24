<?php

namespace App\Filament\Resources\FacilityManagerResource\Pages;

use App\Filament\Resources\FacilityManagerResource;
use App\Models\Role;
use App\Models\User\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListFacilityManagers extends ListRecords
{
    protected static string $resource = FacilityManagerResource::class;

    protected function getTableQuery(): Builder
    {
        $vendors = DB::table('owner_association_vendor')
            ->where('owner_association_id', Filament::getTenant()?->id ?? auth()->user()->owner_association_id)
            ->pluck('vendor_id');
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Admin'){
            return parent::getTableQuery();
        }
        return parent::getTableQuery()->whereIn('id', $vendors);
    }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
        ];
    }
}
