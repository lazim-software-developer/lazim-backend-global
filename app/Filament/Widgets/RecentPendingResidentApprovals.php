<?php

namespace App\Filament\Widgets;

use App\Models\UserApproval;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class RecentPendingResidentApprovals extends BaseWidget
{
    protected static ?int $sort = 5;
    protected static ?string $minHeight = '200px';
    protected static ?string $maxHeight = '200px';
    protected static ?string $heading = 'Recent Pending Resident Approvals';
    protected int | string | array $columnSpan = 6;

    protected function getTableQuery(): Builder
    {
        return UserApproval::query()
            ->where(function ($query) {
                $query->whereNull('status')
                      ->orWhere('status', '');
            })
            ->whereHas('user', function (Builder $query) {
                $query->where('owner_association_id', auth()->user()->owner_association_id);
            })
            ->latest()
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('user.first_name')
                ->label('First Name')
                ->sortable(false),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->default('Pending'),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Submitted On')
                ->date(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                ->url(fn(UserApproval $record): string => route('filament.admin.resources.user-approvals.view', $record)),
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
