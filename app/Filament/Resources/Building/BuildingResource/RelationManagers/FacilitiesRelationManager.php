<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Building\Building;
use App\Models\Master\Facility;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\AttachAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class FacilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'facilities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(50),
                Tables\Columns\ImageColumn::make('icon')
                    ->square()
                    ->alignCenter()
                    ->disk('s3'),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('Remove'),
            ])
            ->headerActions([

                Tables\Actions\AttachAction::make()
                    ->label('Add')
                    ->recordSelect(function () {
                        // Get all the facilities
                        $allFacilities = Facility::all()->pluck('name', 'id')->toArray();

                        // Get the IDs of the selected facilities
                        $selectedFacilityIds = DB::table('building_facility')->pluck('facility_id')->toArray();

                        // Filter out the selected facilities from the list of all facilities
                        $availableFacilities = array_diff_key($allFacilities, array_flip($selectedFacilityIds));

                        return Select::make('recordId')
                            ->label('Facility')
                            ->options($availableFacilities)
                            ->searchable()
                            ->required()
                            ->preload();
                    })
            ]);
    }
}
