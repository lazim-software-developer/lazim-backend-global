<?php

namespace App\Filament\Resources\BuildingsRelationManagerResource\RelationManagers;

use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use Carbon\Carbon;
use DB;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class BuildingsRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('Attach Building')
                    ->modalWidth('lg')
                    ->slideOver()
                    ->form([
                        Select::make('building_id')
                            ->label('Building')
                            ->options(function (RelationManager $livewire) {
                                $pmId = OwnerAssociation::where('email', auth()->user()->email)->pluck('id')[0];

                                $buildingId = DB::table('building_owner_association')
                                    ->where('owner_association_id', $pmId)->pluck('building_id');

                                $existingBuildingIds = DB::table('building_vendor')
                                    ->whereIn('vendor_id', $livewire->ownerRecord)
                                    ->pluck('building_id');

                                return Building::whereIn('id', $buildingId)
                                    ->whereNotIn('id', $existingBuildingIds)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->preload()
                            ->reactive()
                            ->optionsLimit(500)
                            ->live(),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->default(Carbon::now()->format('Y-m-d'))
                                    ->label('From')
                                    ->required()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('end_date', null);
                                    }),

                                DatePicker::make('end_date')
                                    ->label('To')
                                    ->required()
                                    ->after('start_date')
                                    ->validationMessages([
                                        'after' => 'The "to" date must be after the "from" date.',
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $ownerAssociation = OwnerAssociation::where('email', auth()->user()->email)->first();

                        if (!$ownerAssociation) {
                            throw new \Exception('Owner association not found for the current user.');
                        }

                        DB::table('building_vendor')->insert([
                            'building_id'          => $data['building_id'],
                            'contract_id'          => null,
                            'vendor_id'            => $livewire->ownerRecord->id,
                            'start_date'           => $data['start_date'],
                            'active'               => true,
                            'end_date'             => $data['end_date'],
                            'owner_association_id' => $ownerAssociation->id,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove Building')
                    ->modalHeading('Remove Building')
                    ->modalDescription('Performing this action will result in loosing authority of this building!')
                    ->modalSubmitActionLabel('Yes, remove it'),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
