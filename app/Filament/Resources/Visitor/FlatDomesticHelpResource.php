<?php

namespace App\Filament\Resources\Visitor;

use App\Filament\Resources\Visitor\FlatDomesticHelpResource\Pages;
use App\Filament\Resources\Visitor\FlatDomesticHelpResource\RelationManagers;
use App\Models\Visitor\FlatDomesticHelp;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FlatDomesticHelpResource extends Resource
{
    protected static ?string $model = FlatDomesticHelp::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Domestic Help';
    protected static ?string $navigationGroup = 'Flat Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])
                    ->schema([
                    Select::make('flat_id')
                        ->rules(['exists:flats,id'])
                        ->required()
                        ->relationship('flat', 'id')
                        ->searchable()
                        ->placeholder('Flat'),
                    TextInput::make('first_name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('First Name'),
                    TextInput::make('last_name')
                        ->rules(['max:50', 'string'])
                        ->nullable()
                        ->placeholder('Last Name'),
                    TextInput::make('phone')
                        ->rules(['max:10', 'string'])
                        ->required()
                        ->unique(
                            'flat_domestic_helps',
                            'phone',
                            fn(?Model $record) => $record
                        )
                        ->placeholder('Phone'),
                    FileUpload::make('profile_photo')
                        ->nullable()
                        ->disk('s3'),
                    DateTimePicker::make('start_date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Start Date'),
                    DateTimePicker::make('end_date')
                        ->rules(['date'])
                        ->nullable()
                        ->placeholder('End Date'),
                    TextInput::make('role_name')
                        ->rules(['max:50', 'string'])
                        ->required()
                        ->placeholder('Role Name'),
                    Toggle::make('active')
                        ->rules(['boolean'])
                        ->required()

                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([

                Tables\Columns\TextColumn::make('building.name')->label('Building Name')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('flat.id')
                    ->toggleable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('first_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('last_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->toggleable()
                    ->disk('s3'),
                Tables\Columns\TextColumn::make('start_date')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('end_date')
                    ->toggleable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('role_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlatDomesticHelps::route('/'),
            'create' => Pages\CreateFlatDomesticHelp::route('/create'),
            'edit' => Pages\EditFlatDomesticHelp::route('/{record}/edit'),
        ];
    }
}
