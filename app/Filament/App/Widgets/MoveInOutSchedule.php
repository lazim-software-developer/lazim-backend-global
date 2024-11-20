<?php

namespace App\Filament\App\Widgets;

use App\Models\Forms\MoveInOut;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class MoveInOutSchedule extends BaseWidget
{
    protected static ?string $heading          = 'This Week\'s Move In and Out Schedule';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 4;
    public function table(Table $table): Table
    {
        return $table
            ->query(MoveInOut::query()
                    ->with(['building', 'flat'])
                    ->whereIn('building_id', function ($query) {
                        return $query->select('building_id')
                            ->from('building_owner_association')
                            ->where('owner_association_id', auth()->user()?->owner_association_id)
                            ->where('active', true);
                    })
                    ->whereBetween('moving_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ])
                    ->latest()
            )
            ->columns([
                TextColumn::make('ticket_number'),
                TextColumn::make('name'),
                // TextColumn::make('email'),
                // TextColumn::make('phone'),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'move-in'    => 'Move In',
                        'move-out'   => 'Move Out',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'move-in'    => 'success',
                        'move-out'   => 'danger',
                    }),
                TextColumn::make('moving_date')
                    ->date(),
                TextColumn::make('moving_time')
                    ->time(),
                TextColumn::make('building.name'),
                TextColumn::make('flat.property_number')->label('Unit Number'),
            ])
            ->Actions([
                Action::make('view_all')
                    ->label(fn($record) =>
                        'View All ' . ($record->type == 'move-in' ? 'Move In' : 'Move Out') . ' Schedules'
                    )
                    ->url(fn($record): string =>
                        $record->type == 'move-in' ? '/app/move-in-forms-documents' : '/app/move-out-forms-documents'
                    )
                    ->button(),
            ]);
    }
}
