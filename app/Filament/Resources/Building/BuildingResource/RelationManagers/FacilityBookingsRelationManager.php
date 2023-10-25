<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class FacilityBookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'facilityBookings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,])
                    ->schema([

                    // Select::make('building_id')
                    //     ->rules(['exists:buildings,id'])
                    //     ->relationship('building', 'name')
                    //     ->options(function(RelationManager $livewire){
                    //         //dd($livewire->ownerRecord->id);
                    //     })
                    //     ->reactive()
                    //     ->preload()
                    //     ->searchable()
                    //     ->placeholder('Building'),
                    Hidden::make('building_id')
                        ->default(function(RelationManager $livewire){
                            return $livewire->ownerRecord->id;
                        }),
                        
                    Select::make('facility_id')
                        ->rules(['exists:facilities,id'])
                        ->relationship('facility', 'name')
                        ->searchable()
                        ->options(function (callable $get,RelationManager $livewire) {
                            $facilityid = DB::table('building_facility')
                                    ->where('building_facility.building_id', '=', $livewire->ownerRecord->id)
                                    ->select('building_facility.facility_id')
                                    ->pluck('building_facility.facility_id');
                            
                            return DB::table('facilities')
                                    ->whereIn('facilities.id',$facilityid)
                                    ->select('facilities.id','facilities.name')
                                    ->pluck('facilities.name','facilities.id')
                                    ->toArray();
                        })
                        ->required()
                        ->preload()
                        ->placeholder('Facilities'),

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
                    Toggle::make('approved')
                        ->rules(['boolean'])
                        ->required(),
                ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('building_id')
            ->columns([
                TextColumn::make('facility.name')
                    ->searchable()
                    ->label('Facility'),
                TextColumn::make('user.first_name')
                    ->searchable()
                    ->label('User'),
                TextColumn::make('date')
                    ->searchable()
                    ->label('Date'),
                TextColumn::make('start_time')
                    ->searchable()
                    ->label('Start Time'),
                TextColumn::make('end_time')
                    ->searchable()
                    ->label('End Time'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
}
