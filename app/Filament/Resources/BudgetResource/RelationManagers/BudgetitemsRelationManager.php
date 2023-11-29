<?php

namespace App\Filament\Resources\BudgetResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BudgetitemsRelationManager extends RelationManager
{
    protected static string $relationship = 'budgetitems';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Details';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('budget_excl_vat')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service.code')->label('Service Code')->default('NA'),
                TextColumn::make('service.name')->label('Service Name')->default('NA'),
                Tables\Columns\TextColumn::make('budget_excl_vat'),
                Tables\Columns\TextColumn::make('vat_rate'),
                Tables\Columns\TextColumn::make('vat_amount'),
                Tables\Columns\TextColumn::make('total'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
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
