<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;
use App\Filament\Resources\OwnerAssociationInvoiceResource\RelationManagers;
use App\Models\OwnerAssociationInvoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OwnerAssociationInvoiceResource extends Resource
{
    protected static ?string $model = OwnerAssociationInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Invoice';

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
                TextColumn::make('invoice_number'),
                TextColumn::make('date'),
                TextColumn::make('due_date'),
                TextColumn::make('type'),
                TextColumn::make('job'),
                TextColumn::make('month'),
                TextColumn::make('description'),
                TextColumn::make('quantity'),
                TextColumn::make('rate'),
                TextColumn::make('tax'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
                Action::make('download')->url(function( OwnerAssociationInvoice $record){
                    return route('invoice',['data' => $record]);
                })
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
            'index' => Pages\ListOwnerAssociationInvoices::route('/'),
            // 'create' => Pages\CreateOwnerAssociationInvoice::route('/create'),
            // 'view' => Pages\ViewOwnerAssociationInvoice::route('/{record}'),
            // 'edit' => Pages\EditOwnerAssociationInvoice::route('/{record}/edit'),
        ];
    }    
}
