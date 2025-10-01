<?php

namespace App\Filament\Resources\User\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BuildingsRelationManager extends RelationManager
{
    // User model me: public function buildings() { return belongsToMany(...); }
    protected static string $relationship = 'buildings';
    protected static ?string $title = 'Building Access';
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            // optionally eager-load if you show relations inside columns
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Building')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('building_code')
                    ->label('Code')
                    ->toggleable()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('pivot.created_at')
                //     ->label('Attached On')
                //     ->dateTime('d M Y, h:i a')
                //     ->since() // optional: also show "x minutes ago" on hover
                //     ->toggleable(),
            ])
            ->emptyStateHeading('No buildings attached')
            ->emptyStateDescription('Click "Attach Building" to give this user access.')
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Attach Building')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name'])
                    ->recordTitle(fn($record) => $record->name . ($record->building_code ? " ({$record->building_code})" : '')),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Detach')
                    ->requiresConfirmation()
                    ->modalHeading('Remove building access?')
                    ->modalDescription('Is user se is building ka access hat jayega.')
                    ->successNotificationTitle('Detached'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Detach Selected')
                    ->requiresConfirmation(),
            ]);
    }
}
