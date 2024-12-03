<?php

namespace App\Filament\App\Widgets;

use App\Models\Forms\MoveInOut;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class MoveInOutSchedule extends BaseWidget
{
    protected static ?string $heading          = 'This Week\'s Move In and Out Schedule';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 6;
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
                    ->latest()
            )
            ->filters([
                SelectFilter::make('month')
                    ->label('Month')
                    ->options([
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December',
                    ])
                    ->default(null)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $month): Builder => $query->whereMonth('moving_date', $month)
                        );
                    }),
            ])
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
                        'View All '
                    )
                    ->url(fn($record): string =>
                        $record->type == 'move-in' ? '/app/move-in-forms-documents' : '/app/move-out-forms-documents'
                    )
                    ->button(),
            ]);
    }
}
