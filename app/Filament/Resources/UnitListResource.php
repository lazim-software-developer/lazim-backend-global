<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitListResource\Pages\ListUnits;
use App\Models\Forms\MoveInOut;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitListResource extends Resource
{
    protected static ?string $model          = MoveInOut::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        $type = request()->get('type');
        return match ($type) {
            'vacant' => 'Vacant Units',
            'upcoming' => 'Upcoming Units',
            default => 'Units',
        };
    }

    public static function table(Table $table): Table
    {
        $today = Carbon::today();

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($today) {
                $query->whereHas('building', function ($query) {
                    $query->where('owner_association_id', auth()->user()->owner_association_id);
                });

                $type    = request()->get('type');
                $filters = request()->get('filters', []);
                $month   = $filters['month'] ?? null;

                if ($month) {
                    $startOfMonth = Carbon::parse("first day of $month");
                    $endOfMonth   = Carbon::parse("last day of $month");

                    if ($type === 'vacant') {
                        if ($month === Carbon::now()->format('F')) {
                            $query->where('type', 'move-out')
                                ->where('moving_date', '<=', $today)
                                ->where('moving_date', '>=', Carbon::now()->startOfMonth()->subMonth()->endOfMonth());
                        } else {
                            $query->where('type', 'move-out')
                                ->whereBetween('moving_date', [$startOfMonth, $endOfMonth]);
                        }
                    } elseif ($type === 'upcoming') {
                        if ($month === Carbon::now()->format('F')) {
                            $query->where('type', 'move-in')
                                ->where('moving_date', '>=', $today)
                                ->where('moving_date', '<=', $endOfMonth);
                        } else {
                            $query->where('type', 'move-in')
                                ->whereBetween('moving_date', [$startOfMonth, $endOfMonth]);
                        }
                    }
                }
            })
            ->columns([
                TextColumn::make('building.name')
                    ->label('Building')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('flat.property_number')
                    ->label('Unit Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('moving_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->options([
                        'January'   => 'January',
                        'February'  => 'February',
                        'March'     => 'March',
                        'April'     => 'April',
                        'May'       => 'May',
                        'June'      => 'June',
                        'July'      => 'July',
                        'August'    => 'August',
                        'September' => 'September',
                        'October'   => 'October',
                        'November'  => 'November',
                        'December'  => 'December',
                    ])
                    ->default(null)
                    ->query(function (Builder $query, array $data): Builder {
                        $month = $data['value'] ?? null;
                        if ($month) {
                            $startOfMonth = Carbon::parse("first day of $month");
                            $endOfMonth   = Carbon::parse("last day of $month");
                            return $query->whereBetween('moving_date', [$startOfMonth, $endOfMonth]);
                        }
                        return $query;
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnits::route('/'),
        ];
    }

    public static function getSlug(): string
    {
        return 'unit-list';
    }
}
