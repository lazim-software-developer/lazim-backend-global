<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankStatementResource\Pages;
use App\Filament\Resources\BankStatementResource\RelationManagers;
use App\Models\Accounting\OAMReceipts;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankStatementResource extends Resource
{
    protected static ?string $model = OAMReceipts::class;

    protected static ?string $modelLabel = 'Bank Statement';

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
                TextColumn::make('flat.property_number')->label('Unit'),
                TextColumn::make('receipt_number'),
                TextColumn::make('receipt_date')->label('Payment Date'),
                TextColumn::make('payment_mode')->label('Payment Mode'),
                TextColumn::make('noqodi_info')->label('Invoice Number')->formatStateUsing(fn ($state) => json_decode($state)->invoiceNumber),
                TextColumn::make('from_date')->label('General Fund')->formatStateUsing(fn ($record) => $record->noqodi_info ? number_format(json_decode($record->noqodi_info)->generalFundAmount, 2) : 0),
                TextColumn::make('to_date')->label('Reserve Fund')->formatStateUsing(fn ($record) => $record->noqodi_info ? number_format(json_decode($record->noqodi_info)->reservedFundAmount, 2) : 0),
                TextColumn::make('receipt_amount')->label('Total'),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListBankStatements::route('/'),
            // 'create' => Pages\CreateBankStatement::route('/create'),
            // 'edit' => Pages\EditBankStatement::route('/{record}/edit'),
        ];
    }    
}
