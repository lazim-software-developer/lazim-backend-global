<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\FacilityBookingResource\Pages;
use App\Filament\Resources\Building\FacilityBookingResource\RelationManagers;
use App\Models\Building\FacilityBooking;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
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

class FacilityBookingResource extends Resource
{
    protected static ?string $model = FacilityBooking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Facility Bookings';
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

                    Select::make('facility_id')
                        ->rules(['exists:facilities,id'])
                        ->required()
                        ->relationship('facility', 'name')
                        ->searchable()
                        ->placeholder('Facilities'),

                    Select::make('user_id')
                        ->rules(['exists:users,id'])
                        ->required()
                        ->relationship('user', 'first_name')
                        ->searchable()
                        ->placeholder('User'),


                    // Select::make('building_id')
                    //     ->rules(['exists:buildings,id'])
                    //     ->required()
                    //     ->relationship('building', 'name')
                    //     ->searchable()
                    //     ->placeholder('Building'),

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

                    TextInput::make('order_id')
                        ->rules(['max:255', 'string'])
                        ->nullable()
                        ->placeholder('Order Id'),


                    TextInput::make('payment_status')
                        ->rules(['max:50', 'string'])
                        ->nullable()
                        ->placeholder('Payment Status'),


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
                    // Select::make('approved_by')
                    //     ->rules(['exists:users,id'])
                    //     ->relationship('userFacilityBookingApprove', 'first_name')
                    //     ->searchable()
                    //     ->placeholder('User'),



                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                ->toggleable()
                ->limit(50),
                Tables\Columns\TextColumn::make('facility.name')
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
                    ->date(),
                Tables\Columns\TextColumn::make('end_time')
                    ->toggleable()
                    ->date(),
                Tables\Columns\TextColumn::make('order_id')
                    ->toggleable()
                    //->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('payment_status')
                    ->toggleable()
                    //->searchable(true, null, true)
                    ->limit(50),
                Tables\Columns\TextColumn::make('reference_number')
                    ->toggleable(),
                    //->searchable(true, null, true),
                Tables\Columns\IconColumn::make('approved')
                    ->toggleable()
                    ->boolean(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->toggleable()
                //     ->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->toggleable()
                //     ->dateTime(),
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
            'index' => Pages\ListFacilityBookings::route('/'),
            'create' => Pages\CreateFacilityBooking::route('/create'),
            'edit' => Pages\EditFacilityBooking::route('/{record}/edit'),
        ];
    }
}
