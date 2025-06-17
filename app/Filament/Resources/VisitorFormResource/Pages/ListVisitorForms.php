<?php
namespace App\Filament\Resources\VisitorFormResource\Pages;

use App\Filament\Resources\VisitorFormResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListVisitorForms extends ListRecords
{
    protected static string $resource = VisitorFormResource::class;
    protected static ?string $title   = 'Visitors';

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            // Actions\CreateAction::make(),
        ];
    }
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
            return parent::getTableQuery()->whereIn('building_id', $pmBuildings);
        }

        return parent::getTableQuery();

    }
}
