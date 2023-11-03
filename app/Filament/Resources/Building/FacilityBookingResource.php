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
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
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
                    'lg' => 2,
                ])
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
                            ->label('Facility ')
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
                            ->preload()
                            ->searchable()
                            ->placeholder('User'),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->minDate(now()->subYears(150))
                            ->closeOnDateSelection()
                            ->required()
                            ->placeholder('Date'),
                        TimePicker::make('start_time')
                            ->required()
                            ->minDate(now()->subYears(150))
                            ->placeholder('Start Time'),
                        TimePicker::make('end_time')
                            ->after('start_time')
                            ->required()
                            ->placeholder('End Time'),
                        TextInput::make('remarks')
                            ->default('NA')
                            ->required(),
                        TextInput::make('reference_number')
                            ->rules(['numeric'])
                            ->default('0')
                            ->numeric()
                            ->placeholder('References Number'),

                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('60s')
            ->columns([
                Tables\Columns\TextColumn::make('building.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('bookable.name')
                    ->default('NA')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                Tables\Columns\TextColumn::make('date')
                    ->default('NA')
                    ->searchable()
                    ->date(),
                Tables\Columns\TextColumn::make('start_time')
                    ->default('NA')
                    ->time(),
                Tables\Columns\TextColumn::make('end_time')
                    ->default('NA')
                    ->time(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->default('0')
                    ->searchable(),
                Tables\Columns\IconColumn::make('approved')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
            ])
            ->actions([
                Action::make('Update Status')
                    ->button()
                    ->form([
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required()
                            ->live(),
                    ])
                    ->fillForm(fn (FacilityBooking $record): array => [
                        'approved' => $record->status,
                    ])
                    ->action(function (FacilityBooking $record, array $data): void {
                        $record->approved = $data['approved'];
                        $record->save();
                    })
                    ->slideOver()
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
            'view' => Pages\ViewFacilityBooking::route('/{record}'),
            // 'edit' => Pages\EditFacilityBooking::route('/{record}/edit'),
        ];
    }
}
