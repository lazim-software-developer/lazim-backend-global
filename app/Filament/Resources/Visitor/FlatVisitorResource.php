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
    protected static ?string $navigationLabel = 'Guests';
    protected static ?string $navigationGroup = 'Flat Management';
    protected static bool $shouldRegisterNavigation = false;

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
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->required()
                            ->relationship('flat', 'number')
                            ->searchable()
                            ->label('unit Number'),
                        TextInput::make('name')
                            ->rules(['max:50', 'string'])
                            ->required()
                            ->placeholder('Name'),
                        TextInput::make('phone')
                            ->rules(['max:10', 'string'])
                            ->required()
                            ->unique(
                                'flat_visitors',
                                'phone',
                                fn (?Model $record) => $record
                            )
                            ->placeholder('Phone'),

                        Select::make('type')
                            ->required()
                            ->options([
                                'Visitor' => 'Visitor',
                                'Guest' => 'Guest',
                                'DomesticHelp' => 'Domestic Help',
                                'GoodsDelivery' => 'GoodsDelivery'
                            ]),
                        DateTimePicker::make('start_time')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('Start Time'),

                        DateTimePicker::make('end_time')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('End Time'),

                        TextInput::make('verification_code')
                            ->rules(['numeric'])
                            ->required()
                            ->unique(
                                'flat_visitors',
                                'verification_code',
                                fn (?Model $record) => $record
                            )
                            ->numeric()
                            ->placeholder('Verification Code'),

                        Select::make('initiated_by')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('userInitiatedBy', 'first_name')
                            ->searchable()
                            ->placeholder('User Initiated By'),

                        Select::make('approved_by')
                            ->rules(['exists:users,id'])
                            ->required()
                            ->relationship('userApprovedBy', 'first_name')
                            ->searchable()
                            ->placeholder('User Approved By'),

                        TextInput::make('remarks')
                            ->required(),

                        TextInput::make('number_of_visitors')
                            ->rules(['numeric'])
                            ->required()
                            ->numeric()
                            ->placeholder('Number Of Visitors')
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('flat.number')->label('Flat Number')
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
            ->defaultSort('created_at', 'desc')
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
