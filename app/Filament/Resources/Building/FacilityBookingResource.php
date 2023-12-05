<?php

namespace App\Filament\Resources\Building;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use App\Models\Building\FacilityBooking;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Building\FacilityBookingResource\Pages;

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
                            ->options(function () {
                                return Building::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->pluck('name', 'id');
                            })
                            ->reactive()
                            ->required()
                            ->preload()
                            ->searchable()
                            ->placeholder('Building'),

                        Select::make('bookable_id')
                            ->label('Facility ')
                            ->required()
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('building_id')
                    ->relationship('building', 'name')
                    ->searchable()
                    ->preload()
            ])
            ->actions([

                Action::make('Update Status')
                    ->visible(fn($record) => $record->approved === 0)
                    ->button()
                    ->form([
                        Toggle::make('approved')
                            ->rules(['boolean'])
                            ->required()
                            ->live(),
                    ])
                    ->fillForm(fn(FacilityBooking $record): array => [
                        'approved' => $record->approved,
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
        ];
    }
}
