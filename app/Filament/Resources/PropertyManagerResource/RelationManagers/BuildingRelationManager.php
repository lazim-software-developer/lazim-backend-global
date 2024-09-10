<?php

namespace App\Filament\Resources\PropertyManagerResource\RelationManagers;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BuildingRelationManager extends RelationManager
{
    protected static string $relationship = 'buildings';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Building Name')->searchable(),
                Tables\Columns\TextColumn::make('from')->label('From')->searchable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        return $query->whereDoesntHave('ownerAssociations');
                    })
                    ->form(fn(AttachAction $action): array=> [
                        $action->getRecordSelect()->required(),
                        DatePicker::make('from')->default(Carbon::now()->format('d-M-Y')),
                    ]),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
