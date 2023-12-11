<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Service;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ServiceRelationManager extends RelationManager
{
    protected static string $relationship = 'services';
    protected static ?string $modelLabel = 'Service';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Service';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'vendor_service'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->limit(50),
                Tables\Columns\IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->label('Add')
                    ->recordSelect(function (RelationManager $livewire) {
                        $buildingId = $livewire->ownerRecord->id;

                        // Get all the services
                        $allServices = Service::all()->where('type', 'vendor_service')->pluck('id')->toArray();
                        $existingServices = DB::table('building_service')
                            ->where('building_id', $buildingId)
                            ->whereIn('service_id', $allServices)->pluck('service_id')->toArray();
                        $allFacilities = Service::all()->whereNotIn('id', $existingServices)->where('type', 'vendor_service')->pluck('name', 'id')->toArray();
                        return Select::make('recordId')
                            ->label('Service')
                            ->options($allFacilities)
                            ->searchable()
                            ->required()
                            ->preload()
                            ->optionsLimit(200);
                    }),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                // Tables\Actions\DetachAction::make()->label('Remove'),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
