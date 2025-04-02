<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\User\User;
use App\Models\FlatOwners;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use Filament\Facades\Filament;
use App\Models\Building\Complaint;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentRegisterOwner extends BaseWidget
{
    protected static ?int $sort         = 9;
    protected static ?string $heading   = 'Recent Register Owner';
    protected static ?string $maxHeight = '200px';
    // protected int | string | array $columnSpan = 4;

    public static function canView(): bool
    {
        $user = User::find(auth()->user()->id);
        return ($user->can('view_user::owner') || $user->can('view_user::owner'));
    }

    protected function getTableQuery(): Builder
    {
        $buildingId = $this->filters['building'] ?? null;
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
            if ($buildingId) {
            return ApartmentOwner::query()
            ->where('building_id', $buildingId)
            ->whereNull('deleted_at')
            ->latest()
            ->limit(5);
            }else{
                return ApartmentOwner::query()
                ->whereNull('deleted_at')
                ->latest()
                ->limit(5);
            }
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
            if ($buildingId) {
            return ApartmentOwner::query()
            ->where('building_id', $buildingId)
            ->where('owner_association_id', auth()->user()->owner_association_id)
            ->whereNull('deleted_at')
            ->latest()
            ->limit(5);
            }else{
                return ApartmentOwner::query()
                ->where('owner_association_id', auth()->user()->owner_association_id)
                ->whereNull('deleted_at')
                ->latest()
                ->limit(5); 
            }
        }
        // $BuildingId = Building::where('owner_association_id',Filament::getTenant()?->id ?? auth()->user()?->owner_association_id)->pluck('id');
        if ($buildingId) {
            // If building ID is provided, get flats for that specific building
            $flatsId = Flat::where('building_id', $buildingId)
                ->where('owner_association_id', Filament::getTenant()?->id ?? auth()->user()?->owner_association_id)
                ->pluck('id');
        } else {
            // If no building ID is provided, get all flats for the association
            $flatsId = Flat::where('owner_association_id', Filament::getTenant()?->id ?? auth()->user()?->owner_association_id)
                ->pluck('id');
        }
        $flatowners = FlatOwners::whereIn('flat_id', $flatsId)->pluck('owner_id');
        return ApartmentOwner::query()->whereIn('id', $flatowners);
        // Query to get the recent open complaints
        
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->default('NA')
                ->limit(20)
                ->label('Name'),
            Tables\Columns\TextColumn::make('email')
                ->default('NA')
                ->limit(50)
                ->label('Email'),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Submitted On')
                ->date('d-M'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
            ->url(fn(ApartmentOwner $record): string => "/admin/user/owners/{$record->id}/edit"),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function isTableSearchable(): bool
    {
        return false;
    }

    protected function isTableSortable(): bool
    {
        return false;
    }

}
