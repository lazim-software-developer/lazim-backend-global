<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\FlatTenantResource\Pages;
use App\Filament\Resources\Building\FlatTenantResource\RelationManagers;
use App\Models\Building\FlatTenant;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FlatTenantResource extends Resource
{
    protected static ?string $model = FlatTenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Tenants';
    protected static ?string $navigationGroup = 'Building Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(['default' => 0])->schema([
                    Select::make('flat_id')
                        ->rules(['exists:flats,id'])
                        ->required()
                        ->relationship('flat', 'description')
                        ->searchable()
                        ->placeholder('Flat')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('tenant_id')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->placeholder('User')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('primary')
                        ->rules(['boolean'])
                        ->required()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('start_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Start Date')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('end_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('End Date')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->required()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('flat.description')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\IconColumn::make('primary')
                    ->toggleable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('start_date')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('end_date')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\IconColumn::make('active')
                    ->toggleable()
                    ->boolean(),
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
            FlatTenantResource\RelationManagers\ComplaintsRelationManager::class
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlatTenants::route('/'),
            'create' => Pages\CreateFlatTenant::route('/create'),
            'edit' => Pages\EditFlatTenant::route('/{record}/edit'),
        ];
    }    
}
