<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Service;
use App\Models\BuildingService;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BuildingserviceRelationManager extends RelationManager
{
    protected static string $relationship = 'buildingservice';
    protected static ?string $modelLabel = 'Inhouse Service';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Inhouse Service';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_id')
                    ->relationship('service', 'name')
                    ->required()
                    ->disabled()
                    ->preload()
                    ->searchable()
                    ->label('Service'),
                Toggle::make('active')
                    ->label('Active')
                    ->default(1)
                    ->rules(['boolean']),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('service_id', Service::where('type','inhouse')->pluck('id')))
            ->columns([
                TextColumn::make('service.name')->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Action::make('Add')
                    ->button()
                    ->form([
                        Select::make('service_id')
                            ->relationship('service', 'name')
                            ->required()
                            ->options(function (RelationManager $livewire) {
                                $buildingId = $livewire->ownerRecord->id;

                                // Get all the facilities
                                $allServices = Service::all()->where('type', 'inhouse')->pluck('id')->toArray();
                                $existingServices = BuildingService::where('building_id', $buildingId)->whereIn('service_id', $allServices)->pluck('service_id')->toArray();
                                return Service::all()->whereNotIn('id', $existingServices)->where('type', 'inhouse')->pluck('name', 'id')->toArray();
                            })
                            ->preload()
                            ->searchable()
                            ->label('Service'),

                        Hidden::make('building_id')
                            ->default(function (RelationManager $livewire) {
                                return $livewire->ownerRecord->id;
                            }),
                    ])
                    ->action(function (array $data): void {

                        $meeting = BuildingService::create([
                            'building_id' => $data['building_id'],
                            'service_id' => $data['service_id'],
                            'active' => true,
                        ]);
                    })
                    ->slideOver()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ]);
    }
}
