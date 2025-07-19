<?php

namespace App\Filament\Resources\FacilityManagerResource\Pages;

use App\Models\User\User;
use App\Models\Master\Role;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\FacilityManagerResource;

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
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            CreateAction::make()
        ];
    }
}
