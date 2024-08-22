<?php

namespace App\Filament\Widgets;

use App\Models\Building\Complaint;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class RecentOpenComplaints extends BaseWidget
{
    protected static ?int $sort = 7;
    protected static ?string $heading = 'Recent Open Complaints';
    protected int | string | array $columnSpan = 4;

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
                ->default('NA')
                ->limit(20)
                ->label('Complaint'),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Submitted On')
                ->date('d-M'),
        ];
    }

    // protected function getTableActions(): array
    // {
    //     return [
    //         Tables\Actions\EditAction::make()
    //             ->url(fn(Complaint $record): string => route('filament.admin.resources.complaintscomplaints.edit', $record)),
    //     ];
    // }

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
