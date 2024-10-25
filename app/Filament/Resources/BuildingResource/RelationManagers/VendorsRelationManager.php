<?php

namespace App\Filament\Resources\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VendorsRelationManager extends RelationManager
{
    protected static string $relationship = 'vendors';

    protected static function getModelLabel(): string
    {
        return 'Facility Manager';
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Facility Managers';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('tl_number'),
                TextInput::make('tl_expiry'),
                TextInput::make('address_line_1'),
                TextInput::make('landline_number'),
                TextInput::make('website'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('tl_number')
                ->label('TL number'),
                // Tables\Columns\TextColumn::make('tl_expiry'),
                Tables\Columns\TextColumn::make('address_line_1'),
                Tables\Columns\TextColumn::make('landline_number'),
                // Tables\Columns\TextColumn::make('website'),
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
            ]);
    }
}
