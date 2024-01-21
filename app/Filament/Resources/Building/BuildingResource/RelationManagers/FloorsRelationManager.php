<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Forms\Components\FloorQrCode;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class FloorsRelationManager extends RelationManager
{
    protected static string $relationship = 'floors';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('floors')
                    ->numeric()
                    ->disabled()
                    ->placeholder('Floors')
                    ->label('Floor'),
                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()
                    ->disabled()
                    ->searchable()
                    ->label('Building Name'),
                FloorQrCode::make('qr_code')
                    ->label('QR Code'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('floors')
            ->columns([
                TextColumn::make('building.name')->searchable()->label('Building'),
                TextColumn::make('floors')->searchable()->label('Floor'),
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
