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
            ->modifyQueryUsing(function ($query) {
                return $query->where('building_vendor.active', true)
                    ->where('building_vendor.owner_association_id', auth()->user()?->owner_association_id);
            })
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Contract Start Date'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Contract End Date'),
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
                                    ->where('owner_association_id', $pmId)
                                    ->where('active', true)
                                    ->pluck('building_id');

                                $existingBuildingIds = DB::table('building_vendor')
                                    ->whereIn('vendor_id', $livewire->ownerRecord)
                                    ->where('active', true)
                                    ->where('owner_association_id', $pmId)
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
                                    ->label('Contract Start Date')
                                    ->required()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('end_date', null);
                                    }),

                                DatePicker::make('end_date')
                                    ->label('Contract End Date')
                                    ->required()
                                    ->after('start_date')
                                    ->validationMessages([
                                        'after' => 'The "Contract End" date must be after the "Contract Start" date.',
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, RelationManager $livewire): void {
                        $ownerAssociation = OwnerAssociation::where('email', auth()->user()->email)->first();

                        if (!$ownerAssociation) {
                            throw new \Exception('Owner association not found for the current user.');
                        }

                        $existingRecord = DB::table('building_vendor')
                            ->where('building_id', $data['building_id'])
                            ->where('owner_association_id', $ownerAssociation->id)
                            ->where('vendor_id', $livewire->ownerRecord->id)
                            ->first();

                        if ($existingRecord) {
                            // Update existing record
                            DB::table('building_vendor')
                                ->where('building_id', $data['building_id'])
                                ->where('vendor_id', $livewire->ownerRecord->id)
                                ->update([
                                    'start_date' => $data['start_date'],
                                    'end_date'   => $data['end_date'],
                                    'active'     => true,
                                ]);
                        } else {
                            // Create new record
                            DB::table('building_vendor')->insert([
                                'building_id'          => $data['building_id'],
                                'contract_id'          => null,
                                'vendor_id'            => $livewire->ownerRecord->id,
                                'start_date'           => $data['start_date'],
                                'active'               => true,
                                'end_date'             => $data['end_date'],
                                'owner_association_id' => $ownerAssociation->id,
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove Building')
                    ->modalHeading('Remove Building')
                    ->modalDescription('Performing this action will result in losing authority of this building!')
                    ->modalSubmitActionLabel('Yes, remove it')
                    ->action(function ($record, RelationManager $livewire) {
                        // Instead of detaching, update the record to inactive
                        DB::table('building_vendor')
                            ->where('building_id', $record->id)
                            ->where('vendor_id', $livewire->ownerRecord->id)
                            ->update([
                                'active'   => false,
                                'end_date' => now()->format('Y-m-d'),
                            ]);
                    }),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
