<?php

namespace App\Filament\Resources\Visitor;

use App\Filament\Resources\Visitor\FlatVisitorResource\Pages;
use App\Filament\Resources\Visitor\FlatVisitorResource\RelationManagers;
use App\Models\Visitor\FlatVisitor;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FlatVisitorResource extends Resource
{
    protected static ?string $model = FlatVisitor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Visitors';
    protected static ?string $navigationGroup = 'Visitor Management';


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

                    TextInput::make('name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Name')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('phone')
                        ->rules(['max:10', 'string'])
                        ->required()
                        ->unique(
                            'flat_visitors',
                            'phone',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Phone')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('type')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Type')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('start_time')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Start Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    DateTimePicker::make('end_time')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('End Time')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('verification_code')
                        ->rules(['numeric'])
                        ->required()
                        ->unique(
                            'flat_visitors',
                            'verification_code',
                            fn(?Model $record) => $record
                        )
                        ->numeric()
                        ->placeholder('Verification Code')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('initiated_by')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('userInitiatedBy', 'first_name')
                        ->searchable()
                        ->placeholder('User Initiated By')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    Select::make('approved_by')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('userApprovedBy', 'first_name')
                        ->searchable()
                        ->placeholder('User Approved By')
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    KeyValue::make('remarks')
                        ->required()
                        ->required()
                        ->columnSpan([
                            'default' => 12,
                            'md' => 12,
                            'lg' => 12,
                        ]),

                    TextInput::make('number_of_visitors')
                        ->rules(['numeric'])
                        ->required()
                        ->numeric()
                        ->placeholder('Number Of Visitors')
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
                Tables\Columns\TextColumn::make('name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('start_time')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('end_time')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('verification_code')
                    ->toggleable()
                    ->searchable(true, null, true),
                Tables\Columns\TextColumn::make('userInitiatedBy.first_name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('userApprovedBy.first_name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('number_of_visitors')
                    ->toggleable()
                    ->searchable(true, null, true),
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
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlatVisitors::route('/'),
            'create' => Pages\CreateFlatVisitor::route('/create'),
            'edit' => Pages\EditFlatVisitor::route('/{record}/edit'),
        ];
    }    
}
