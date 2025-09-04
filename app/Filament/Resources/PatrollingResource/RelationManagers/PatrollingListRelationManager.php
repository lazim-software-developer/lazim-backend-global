<?php

namespace App\Filament\Resources\PatrollingResource\RelationManagers;

// use Filament\Forms;
// use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatrollingListRelationManager extends RelationManager
{
    protected static string $relationship = 'patrollingList';

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\TextInput::make('patrolling_record_id')
    //                 ->required()
    //                 ->maxLength(255),
    //         ]);
    // }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('patrolling_record_id')
            ->columns([
                // Tables\Columns\TextColumn::make('patrolling_record_id'),
                Tables\Columns\TextColumn::make('floor.floors')->label('Floor'),
                Tables\Columns\TextColumn::make('location_name')->label('Location'),
                Tables\Columns\TextColumn::make('patrolled_at')->label('Patrolled At'),
                Tables\Columns\TextColumn::make('is_completed')->label('Status')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Completed',
                        0 => 'In-Progress',
                        default => 'In-complete',
                    })
                    ->color(fn ($state) => match ($state) {
                        1 => 'success',
                        0 => 'warning',
                        default => 'danger',
                    })
                    ->icon(fn ($state) => match ($state) {
                        1 => 'heroicon-o-check-circle',
                        0 => 'heroicon-o-minus-circle',
                        default => 'heroicon-o-x-circle',
                    }),
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
            ]);
    }
}
