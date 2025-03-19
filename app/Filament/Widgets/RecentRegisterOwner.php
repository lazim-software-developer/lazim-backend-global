<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\User\User;
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
        // Query to get the recent open complaints
        return ApartmentOwner::query()
            ->whereHas('building', function (Builder $query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id);
            })
            ->whereNull('deleted_at')
            ->latest()
            ->limit(5);
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
