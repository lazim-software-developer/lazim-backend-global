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
use Illuminate\Support\Facades\DB;

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

                $pmFlats = DB::table('property_manager_flats')
                    ->where('owner_association_id', auth()->user()->owner_association_id)
                    ->where('active', true)
                    ->pluck('flat_id')
                    ->toArray();

                $query->whereIn('flat_id', $pmFlats);

                $type = request()->get('type');

                if ($type === 'vacant') {
                    $query->where('type', 'move-out')
                          ->where('moving_date', '<', $today);
                } elseif ($type === 'upcoming') {
                    $query->where('type', 'move-in')
                          ->where('moving_date', '>', $today);
                }

                $filters = request()->get('filters', []);
                $month = $filters['month'] ?? null;

                if ($month) {
                    $startOfMonth = Carbon::parse("first day of $month");
                    $endOfMonth = Carbon::parse("last day of $month");

                    if ($type === 'vacant' || $type === 'upcoming') {
                        $query->whereBetween('moving_date', [$startOfMonth, $endOfMonth]);
                    }
                }

                return $query;
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
