<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitListResource\Pages\ListUnits;
use App\Models\Forms\MoveInOut;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnitListResource extends Resource
{
    protected static ?string $model = MoveInOut::class;
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

                $type = request()->get('type');

                if ($type === 'vacant') {
                    $query->where('type', 'move-out')
                          ->where('moving_date', '<', $today);
                } elseif ($type === 'upcoming') {
                    $query->where('type', 'move-in')
                          ->where('moving_date', '>=', $today);
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
