<?php

namespace App\Filament\Resources\Building\BuildingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class BuildingvendorRelationManager extends RelationManager
{
    protected static string $relationship = 'buildingvendor';
    protected static ?string $modelLabel = 'Vendors';
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Vendors';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('vender_name')
                    ->relationship('vendor','name')
                    ->label('Vendor name'),
                Select::make('vender_tl_number')
                    ->relationship('vendor','tl_number')
                    ->label('Vendor tl_number'),
                Select::make('vender_tl_expiry')
                    ->relationship('vendor','tl_expiry')
                    ->label('Vendor tl_expiry'),
                Select::make('vender_address_line_1')
                    ->relationship('vendor','address_line_1')
                    ->label('Vendor address_line_1'),
                Select::make('vender_landline_number')
                    ->relationship('vendor','landline_number')
                    ->label('Vendor landline_number'),
                Select::make('vender_website')
                    ->relationship('vendor','website')
                    ->label('Vendor website'),
                Select::make('vender_fax')
                    ->relationship('vendor','fax')
                    ->label('Vendor fax'),
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract.amount')->label('Contract amount')->default('NA'),
                TextColumn::make('contract.contract_type')->label('Contract type')->default('NA'),
                TextColumn::make('vendor.name'),
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
