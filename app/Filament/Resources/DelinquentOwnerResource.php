<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DelinquentOwnerResource\Pages;
use App\Filament\Resources\DelinquentOwnerResource\RelationManagers;
use App\Models\Building\Building;
use App\Models\DelinquentOwner;
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

class DelinquentOwnerResource extends Resource
{
    protected static ?string $model = DelinquentOwner::class;

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
                TextColumn::make('owner.name')->limit(25),
                TextColumn::make('last_payment_date')->default('NA'),
                TextColumn::make('last_payment_amount')->default('NA'),
                TextColumn::make('outstanding_balance'),
                TextColumn::make('quarter_1_balance'),
                TextColumn::make('quarter_2_balance'),
                TextColumn::make('quarter_3_balance')->default('NA'),
                TextColumn::make('quarter_4_balance')->default('NA'),
                TextColumn::make('invoice_pdf_link')->label('invoice_file')->formatStateUsing(fn ($state) => '<a href="'. $state .'" target="_blank">LINK</a>')
                ->html(),
                
            ])
            ->filters([
                Filter::make('invoice_date')
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
            'index' => Pages\ListDelinquentOwners::route('/'),
            // 'create' => Pages\CreateDelinquentOwner::route('/create'),
            // 'edit' => Pages\EditDelinquentOwner::route('/{record}/edit'),
        ];
    }    
}
