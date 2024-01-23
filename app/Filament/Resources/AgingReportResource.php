<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgingReportResource\Pages;
use App\Filament\Resources\AgingReportResource\RelationManagers;
use App\Models\AgingReport;
use App\Models\Building\Building;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AgingReportResource extends Resource
{
    protected static ?string $model = AgingReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flat.property_number')->label('Unit'),
                TextColumn::make('owner.name')->label('Owner')->limit(20),
                TextColumn::make('outstanding_balance')->label('Outstanding Balance'),
                TextColumn::make('balance_1')->label("Aged Balance 0-90"),
                TextColumn::make('balance_2')->label('Aged Balance 91-180'),
                TextColumn::make('balance_3')->label('Aged Balance 180-270'),
                TextColumn::make('balance_4')->label('Aged Balance 270-360'),
                TextColumn::make('over_balance')->label('Aged Balance Above 360')
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        Select::make('year')
                        ->searchable()
                        ->placeholder('Select Year')
                        ->options(array_combine(range(now()->year, 2018), range(now()->year, 2018))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['year'])) {
                                return $query
                                    ->when(
                                        $data['year'],
                                        fn (Builder $query, $year) => $query->where('year', $year)
                                    );
                        }

                            return $query;
                        }),
                Filter::make('Building')
                    ->form([
                        Select::make('building')
                        ->searchable()
                        ->options(function () {
                            $oaId = auth()->user()->owner_association_id;
                            return Building::where('owner_association_id', $oaId)
                                ->pluck('name', 'id');
                        })
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['building'],
                                fn (Builder $query, $building_id): Builder => $query->where('building_id', $building_id),
                            );
                        }),
                    ],layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgingReports::route('/'),
            // 'create' => Pages\CreateAgingReport::route('/create'),
            // 'edit' => Pages\EditAgingReport::route('/{record}/edit'),
        ];
    }    
}
