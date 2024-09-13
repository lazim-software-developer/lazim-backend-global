<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use App\Models\Master\Facility;
use Filament\Forms;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class FacilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'facilities';
    protected static ?string $title       = 'Amenities';
    protected static ?string $modelLabel       = 'Amenities';

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
                        FileUpload::make('icon')
                        ->disk('s3')
                        ->directory('dev')
                        ->downloadable(true)
                        ->openable(true)
                        ->columnSpan([
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 2,
                        ]),
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
            ->defaultSort('created_at', 'desc')
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
                            ->label('Amenities')
                            ->options($allFacilities)
                            ->searchable()
                            ->required()
                            ->preload();
                    })->form(fn (AttachAction $action, RelationManager $livewire): array => [
                        $action->getRecordSelect(),
                        Hidden::make('owner_association_id')
                        ->default(function()use ($action, $livewire){
                            $buildingId = $livewire->ownerRecord->id;
                            $oa_id = DB::table('building_owner_association')->where('building_id', $buildingId)->where('active', true)->first()?->owner_association_id;
                            return $oa_id;
                        }),
                    ])


            ]);
    }
}
