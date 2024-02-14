<?php

namespace App\Filament\Resources\TechnicianAssetsResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class AssetMaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'assetMaintenances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('maintenance_date')
                    ->required(),
                Select::make('user_id')
                    ->relationship('maintainer','first_name'),
                Select::make('building_id')
                    ->relationship('building','name'),
                TextInput::make('status'),
                ViewField::make('Service history')
                    ->view('forms.components.comments')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('maintenance_date'),
                TextColumn::make('comment')->formatStateUsing(function ($state) {
                    $decodedState = json_decode($state);
                    return "Before: {$decodedState->before}, After: {$decodedState->after}";
                }),
                TextColumn::make('maintainer.first_name'),
                TextColumn::make('building.name'),
                TextColumn::make('status'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
