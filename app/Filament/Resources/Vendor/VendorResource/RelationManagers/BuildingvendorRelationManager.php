<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BuildingvendorRelationManager extends RelationManager
{
    protected static string $relationship = 'buildingvendor';
    protected static ?string $modelLabel  = 'Buildings';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Buildings';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('building_name')
                    ->relationship('building', 'name')
                    ->label('building name'),
                Select::make('building_property_group_id')
                    ->relationship('building', 'property_group_id')
                    ->label('building property_group_id'),
                Select::make('building_address_line1')
                    ->relationship('building', 'address_line1')
                    ->label('building address_line1'),
                Select::make('building_area')
                    ->relationship('building', 'area')
                    ->label('building area'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract.amount')->label('Contract amount')->default('--'),
                TextColumn::make('contract.contract_type')->label('Contract type')->default('--'),
                TextColumn::make('building.name'),
                IconColumn::make('active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
