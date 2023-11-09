<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Master\Facility;
use Filament\Forms;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class FacilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'facilities';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
              TextInput::make('name'),
              Toggle::make('active'),
              FileUpload::make('icon'),
                    ])
            ]);
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DetachAction::make()->label('Remove'),
            ])
            ->headerActions([

                Tables\Actions\AttachAction::make()
                    ->label('Add')
                    ->recordSelect(function (RelationManager $livewire) {
                        $buildingId = $livewire->ownerRecord->id;

                        // Get all the facilities
                        $allFacilities = Facility::all()->pluck('id')->toArray();
                        $existingFacility =  DB::table('building_facility')
                            ->where('building_id', $buildingId)
                            ->whereIn('facility_id', $allFacilities)->pluck('facility_id')->toArray();
                        $allFacilities = Facility::all()->whereNotIn('id', $existingFacility)->pluck('name', 'id')->toArray();
                        return Select::make('recordId')
                            ->label('Facility')
                            ->options($allFacilities)
                            ->searchable()
                            ->required()
                            ->preload();
                    })

            ]);
    }
}
