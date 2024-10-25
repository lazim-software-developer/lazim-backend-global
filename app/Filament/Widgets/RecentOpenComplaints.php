<?php

namespace App\Filament\Widgets;

use App\Models\Building\Complaint;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentOpenComplaints extends BaseWidget
{
    protected static ?int $sort         = 9;
    protected static ?string $heading   = 'Recent Open Complaints';
    protected static ?string $maxHeight = '200px';
    // protected int | string | array $columnSpan = 4;

    public static function canView(): bool
    {
        $user = User::find(auth()->user()->id);
        return ($user->can('view_any_complaintscomplaint') || $user->can('view_any_helpdeskcomplaint'));
    }

    protected function getTableQuery(): Builder
    {
        // Query to get the recent open complaints
        return Complaint::query()
            ->where('status', 'open')
            ->where('complaint_type', 'tenant_complaint')
            ->whereHas('building', function (Builder $query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id);
            })
            ->latest()
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('complaint')
                ->default('--')
                ->limit(20)
                ->label('Complaint'),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Submitted On')
                ->date('d-M'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make()
                ->url(fn(Complaint $record): string => route('filament.admin.resources.complaintscomplaints.edit', [
                    'tenant' => Filament::getTenant()->slug,
                    'record' => $record->id,
                ])),
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
