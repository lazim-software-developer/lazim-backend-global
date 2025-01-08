<?php

namespace App\Filament\Resources\AnnouncementResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\AnnouncementResource;
use Illuminate\Support\Facades\DB;

class ListAnnouncements extends ListRecords
{
    protected static string $resource = AnnouncementResource::class;
    protected static ?string $title = 'Notice board';
    protected static ?string $modeLabel = 'Notice board';

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()->where('is_announcement', 1);

        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Property Manager') {
            $pmBuildings = DB::table('building_owner_association')
                ->where('owner_association_id', auth()->user()?->owner_association_id)
                ->where('active', true)
                ->pluck('building_id');

            return $query->whereHas('building', function ($query) use ($pmBuildings) {
                $query->whereIn('buildings.id', $pmBuildings);
            });
        } elseif (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
            return $query->where('owner_association_id', auth()->user()?->owner_association_id);
        }

        return $query;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label('New Notice board'),
        ];
    }
}
