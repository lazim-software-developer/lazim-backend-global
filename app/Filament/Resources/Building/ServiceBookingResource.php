<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\ServiceBookingResource\Pages;
use App\Filament\Resources\Building\ServiceBookingResource\RelationManagers;
use App\Models\Building\FacilityBooking;
use App\Models\Building\ServiceBooking;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ServiceBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Service Bookings';
    protected static ?string $navigationGroup = 'Property Management';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])
                    ->schema([

                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->reactive()
                        ->preload()
                        ->searchable()
                        ->placeholder('Building'),
                    
                    Select::make('bookable_id')
                        ->options(
                            DB::table('services')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                    ->searchable()
                    ->preload()
                    ->placeholder('Service'),

                    Hidden::make('bookable_type')
                        ->default('App\Models\Master\Service'),

                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->placeholder('User'),
                    DatePicker::make('date')
                        ->rules(['date'])
                        ->required()
                        ->placeholder('Date'),
                    TimePicker::make('start_time')
                        ->required()
                        ->placeholder('Start Time'),
                    TimePicker::make('end_time')
                        ->required()
                        ->placeholder('End Time'),
                    TextInput::make('remarks')
                        ->required(),
                    TextInput::make('reference_number')
                        ->rules(['numeric'])
                        ->required()
                        ->numeric()
                        ->placeholder('References Number'),
                    Toggle::make('approved')
                        ->rules(['boolean'])
                        ->required(),
                ]),

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('building.name')
            ->toggleable()
            ->limit(50),
            Tables\Columns\TextColumn::make('bookable.name')
                ->toggleable()
                ->limit(50),
            Tables\Columns\TextColumn::make('user.first_name')
                ->toggleable()
                ->searchable(isIndividual: false, isGlobal: true)
                ->limit(50),
            Tables\Columns\TextColumn::make('date')
                ->toggleable()
                ->date(),
            Tables\Columns\TextColumn::make('start_time')
                ->toggleable()
                ->time(),
            Tables\Columns\TextColumn::make('reference_number')
                ->toggleable(),
            Tables\Columns\IconColumn::make('approved')
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
            'index' => Pages\ListServiceBookings::route('/'),
            'create' => Pages\CreateServiceBooking::route('/create'),
            'edit' => Pages\EditServiceBooking::route('/{record}/edit'),
        ];
    }    
}
