<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetRelationManager extends RelationManager
{
    protected static string $relationship = 'budgets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('total')
            ->columns([
                TextColumn::make('budget_excl_vat')
                    ->label('Budget Excl Vat')
                    ->default('--'),
                TextColumn::make('vat_rate')
                    ->label('Vat Rate')
                    ->default('--'),
                TextColumn::make('vat_amount')
                    ->label('Vat Amount')
                    ->default('--'),
                TextColumn::make('total')
                    ->label('Total')
                    ->default('--'),
                TextColumn::make('rate')
                    ->label('Rate')
                    ->default('--'),
                TextColumn::make('building.name')
                    ->default('--')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('budget_period')
                    ->label('Budget Period')
                    ->default('--'),
                TextColumn::make('budget_from')
                    ->date(),
                TextColumn::make('budget_to')
                    ->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
            ]);
    }
}
