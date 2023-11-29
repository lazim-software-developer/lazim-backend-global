<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorLedgersResource\Pages;
use App\Filament\Resources\VendorLedgersResource\RelationManagers;
use App\Models\Accounting\Invoice;
use App\Models\Building\Building;
use App\Models\VendorLedgers;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class VendorLedgersResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Service Provider Ledgers';

    protected static ?string $navigationGroup = 'Ledgers';

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
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->default("NA")
                    ->label('Invoice Number'),
                ImageColumn::make('document')
                    ->square(),
                TextColumn::make('invoice_amount')
                    ->label('Invoice Amount'),
                TextColumn::make('vendor.name')
                    ->searchable()
                    ->label('Vendor Name'),
                TextColumn::make('status')
                    ->searchable(),
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
            'index' => Pages\ListVendorLedgers::route('/'),
            // 'create' => Pages\CreateVendorLedgers::route('/create'),
            // 'edit' => Pages\EditVendorLedgers::route('/{record}/edit'),
        ];
    }    
}
