<?php

namespace App\Filament\Resources\Building;

use App\Filament\Resources\Building\FacilityBookingResource\Pages;
use App\Filament\Resources\Building\FacilityBookingResource\RelationManagers;
use App\Models\Building\FacilityBooking;
use App\Models\Master\Facility;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
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
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

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

                    Select::make('building_id')
                        ->rules(['exists:buildings,id'])
                        ->relationship('building', 'name')
                        ->reactive()
                        ->preload()
                        ->searchable()
                        ->placeholder('Building'),
                        
                    // Select::make('facility_id')
                    //     ->rules(['exists:facilities,id'])
                    //     ->relationship('facility', 'name')
                    //     ->searchable()
                    //     ->options(function (callable $get) {
                    //         $facilityid = DB::table('building_facility')
                    //                 ->where('building_facility.building_id', '=', $get('building_id'))
                    //                 ->select('building_facility.facility_id')
                    //                 ->pluck('building_facility.facility_id');
                            
                    //         return DB::table('facilities')
                    //                 ->whereIn('facilities.id',$facilityid)
                    //                 ->select('facilities.id','facilities.name')
                    //                 ->pluck('facilities.name','facilities.id')
                    //                 ->toArray();
                    //     })
                    //     ->required()
                    //     ->preload()
                    //     ->placeholder('Facilities'),
                    
                    Select::make('bookable_id')
                        ->options(
                            DB::table('facilities')
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                    ->searchable()
                    ->preload()
                    ->placeholder('Facility'),

                    Hidden::make('bookable_type')
                        ->default('App\Models\Master\Facility'),

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
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                ->toggleable()
                ->default('NA')
                ->limit(50),
                Tables\Columns\TextColumn::make('bookable.name')
                    ->toggleable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->toggleable()
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('date')
                    ->toggleable()
                    ->default('NA')
                    ->date(),
                Tables\Columns\TextColumn::make('start_time')
                    ->toggleable()
                    ->default('NA')
                    ->time(),
                Tables\Columns\TextColumn::make('end_time')
                    ->toggleable()
                    ->default('NA')
                    ->time(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->default('NA')
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
            'index' => Pages\ListFacilityBookings::route('/'),
            'create' => Pages\CreateFacilityBooking::route('/create'),
            'edit' => Pages\EditFacilityBooking::route('/{record}/edit'),
        ];
    }
}
