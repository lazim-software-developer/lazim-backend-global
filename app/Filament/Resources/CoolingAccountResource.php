<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoolingAccountResource\Pages;
use App\Filament\Resources\CoolingAccountResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\CoolingAccount;
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
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class CoolingAccountResource extends Resource
{
    protected static ?string $model = CoolingAccount::class;

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
                TextColumn::make('building.name'),
                TextColumn::make('flat.property_number'),
                TextColumn::make('date')->date(),
                TextColumn::make('opening_balance'),
                TextColumn::make('consumption'),
                TextColumn::make('demand_charge'),
                TextColumn::make('security_deposit'),
                TextColumn::make('billing_charges'),
                TextColumn::make('other_charges'),
                TextColumn::make('receipts'),
                TextColumn::make('closing_balance'),
            ])
            ->filters([
                Filter::make('date')
                            ->form([
                                DateRangePicker::make('Date')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (isset($data['Date'])) {
                                    $dateRange = explode(' - ', $data['Date']);
                            
                                    if (count($dateRange) === 2) {
                                        $from = \Carbon\Carbon::createFromFormat('d/m/Y', $dateRange[0])->format('Y-m-d');
                                        $until = \Carbon\Carbon::createFromFormat('d/m/Y', $dateRange[1])->format('Y-m-d');
                            
                                        return $query
                                            ->when(
                                                $from,
                                                fn (Builder $query, $date) => $query->whereDate('date', '>=', $date)
                                            )
                                            ->when(
                                                $until,
                                                fn (Builder $query, $date) => $query->whereDate('date', '<=', $date)
                                            );
                                    }
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
                // Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListCoolingAccounts::route('/'),
            // 'create' => Pages\CreateCoolingAccount::route('/create'),
            // 'view' => Pages\ViewCoolingAccount::route('/{record}'),
            // 'edit' => Pages\EditCoolingAccount::route('/{record}/edit'),
        ];
    }    
}
