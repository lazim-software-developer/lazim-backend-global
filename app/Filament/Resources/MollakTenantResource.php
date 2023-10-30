<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MollakTenantResource\Pages;
use App\Filament\Resources\MollakTenantResource\RelationManagers;
use App\Models\MollakTenant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MollakTenantResource extends Resource
{
    protected static ?string $model = MollakTenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Master';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('contract_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('emirates_id')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('license_number')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('mobile')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('email')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('start_date')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('end_date')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('contract_status')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA'),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA'),
            ])
            ->filters([
                //
            ])
            // ->actions([
            //     Tables\Actions\EditAction::make(),
            // ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
            // ->emptyStateActions([
            //     Tables\Actions\CreateAction::make(),
            // ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMollakTenants::route('/'),
            //'create' => Pages\CreateMollakTenant::route('/create'),
            //'edit' => Pages\EditMollakTenant::route('/{record}/edit'),
        ];
    }    
}
