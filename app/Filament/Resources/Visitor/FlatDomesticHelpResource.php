<?php

namespace App\Filament\Resources\Visitor;

use App\Filament\Resources\Visitor\FlatDomesticHelpResource\Pages;
use App\Models\Visitor\FlatDomesticHelp;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FlatDomesticHelpResource extends Resource
{
    protected static ?string $model = FlatDomesticHelp::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Domestic Help';
    protected static ?string $navigationGroup = 'Flat Management';
    protected static bool $shouldRegisterNavigation = false;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2
                ])
                    ->schema([
                        Select::make('flat_id')
                            ->rules(['exists:flats,id'])
                            ->required()
                            ->relationship('flat', 'number')
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
                                fn (?Model $record) => $record
                            )
                            ->placeholder('Phone'),
                        FileUpload::make('profile_photo')
                            ->nullable()
                            ->disk('s3'),
                        DatePicker::make('start_date')
                            ->rules(['date'])
                            ->required()
                            ->placeholder('Start Date'),
                        DatePicker::make('end_date')
                            ->rules(['date'])
                            ->nullable()
                            ->placeholder('End Date'),
                        TextInput::make('role_name')
                            ->rules(['max:50', 'string'])
                            ->required()
                            ->placeholder('Role Name'),
                        Hidden::make('active')
                            ->default(1),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                TextColumn::make('flat.number')->label('Flat Number')
                    ->toggleable()
                    ->limit(50),
                TextColumn::make('first_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                TextColumn::make('last_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                TextColumn::make('phone')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
                ImageColumn::make('profile_photo')
                    ->toggleable()
                    ->circular()
                    ->disk('s3'),
                TextColumn::make('start_date')
                    ->toggleable()
                    ->date(),
                TextColumn::make('end_date')
                    ->toggleable()
                    ->date(),
                TextColumn::make('role_name')
                    ->toggleable()
                    ->searchable(true, null, true)
                    ->limit(50),
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
            'index'  => Pages\ListFlatDomesticHelps::route('/'),
            'create' => Pages\CreateFlatDomesticHelp::route('/create'),
            'edit'   => Pages\EditFlatDomesticHelp::route('/{record}/edit'),
        ];
    }
}
