<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OAMReceiptsResource\Pages;
use App\Filament\Resources\OAMReceiptsResource\RelationManagers;
use App\Models\Accounting\OAMReceipts;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OAMReceiptsResource extends Resource
{
    protected static ?string $model = OAMReceipts::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOAMReceipts::route('/'),
            // 'create' => Pages\CreateOAMReceipts::route('/create'),
            // 'view' => Pages\ViewOAMReceipts::route('/{record}'),
            // 'edit' => Pages\EditOAMReceipts::route('/{record}/edit'),
        ];
    }    
}
