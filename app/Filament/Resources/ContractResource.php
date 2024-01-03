<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Vendor\Contract;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ContractResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ContractResource\RelationManagers;
use Illuminate\Support\Facades\DB;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Oam';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('contract_type')
                            ->options([
                                'annual maintenance contract' => 'Annual Maintenance Contract',
                                'onetime' => 'OneTime',
                            ])
                            ->disabled()
                            ->searchable()
                            ->required()
                            ->label('Contract Type'),
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->reactive()
                            ->required()
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->placeholder('Building'),
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->reactive()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->disabled()
                            ->placeholder('Service'),
                        DatePicker::make('start_date')
                            ->required()
                            ->rules(['date'])
                            ->disabled()
                            ->placeholder('Start Date'),
                        DatePicker::make('end_date')
                            ->required()
                            ->rules(['date'])
                            ->disabled()
                            ->placeholder('End Date'),
                        FileUpload::make('document_url')
                            ->required()
                            ->disk('s3')
                            ->directory('dev')
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                        TextInput::make('amount')
                            ->numeric(true)
                            ->prefix('AED')
                            ->required(),
                        TextInput::make('budget_amount')
                            ->numeric(true)
                            ->prefix('AED')
                            ->required(),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->reactive()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->disabled()
                            ->placeholder('Vendor'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_type')->label('Contract Type')->searchable(),
                Tables\Columns\TextColumn::make('start_date')->label('Start Date'),
                Tables\Columns\TextColumn::make('end_date')->label('End Date'),
                Tables\Columns\TextColumn::make('amount')->label('Amount'),
                Tables\Columns\TextColumn::make('budget_amount')->label('Budget Amount'),
                // Tables\Columns\ImageColumn::make('document_url')->square()->disk('s3')->label('Document'),
                Tables\Columns\TextColumn::make('building.name')->label('Building')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Service')->searchable(),
                Tables\Columns\TextColumn::make('vendor.name')->label('Vendor')->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
