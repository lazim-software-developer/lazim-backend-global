<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\FlatTenantResource\Pages;
use App\Models\Building\FlatTenant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FlatTenantResource extends Resource
{
    protected static ?string $model = FlatTenant::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Tenants';
    protected static ?string $navigationGroup = 'Flat Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2])
                    ->schema([
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->required()
                            ->relationship('flat', 'property_number')
                            ->searchable()
                            ->placeholder('Flat'),
                        Select::make('tenant_id')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('user', 'first_name')
                            ->searchable()
                            ->placeholder('User'),
                        DatePicker::make('start_date')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('Start Date'),
                        DatePicker::make('end_date')
                            ->rules(['date'])
                            ->placeholder('End Date'),
                        Toggle::make('primary')
                            ->rules(['boolean']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('flat.property_number')
                    ->toggleable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('user.first_name')
                    ->toggleable()
                    ->default('NA')
                    ->limit(50),

                TextColumn::make('start_date')
                    ->toggleable()
                    ->date(),
                TextColumn::make('end_date')
                    ->toggleable()
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            FlatTenantResource\RelationManagers\DocumentsRelationManager::class,
            FlatTenantResource\RelationManagers\ComplaintsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFlatTenants::route('/'),
            'create' => Pages\CreateFlatTenant::route('/create'),
            'edit'   => Pages\EditFlatTenant::route('/{record}/edit'),
        ];
    }
}
