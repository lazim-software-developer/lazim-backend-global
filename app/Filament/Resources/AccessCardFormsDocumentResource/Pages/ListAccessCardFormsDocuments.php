<?php
namespace App\Filament\Resources\AccessCardFormsDocumentResource\Pages;

use App\Filament\Resources\AccessCardFormsDocumentResource;
use DB;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAccessCardFormsDocuments extends ListRecords
{
    protected static string $resource = AccessCardFormsDocumentResource::class;
    protected static ?string $title   = 'Access card';
    protected function getTableQuery(): Builder
    {
        $role = auth()->user()->role->name;

        $pmBuildings = DB::table('building_owner_association')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('building_id');

        $pmFlats = DB::table('property_manager_flats')
            ->where('owner_association_id', auth()->user()?->owner_association_id)
            ->where('active', true)
            ->pluck('flat_id')
            ->toArray();

        if ($role == 'Property Manager') {
            return parent::getTableQuery()->whereIn('flat_id', $pmFlats);
        } elseif ($role == 'OA') {
            return parent::getTableQuery()->where->whereIn('building_id', $pmBuildings);
        }

        return parent::getTableQuery();

    }
    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
