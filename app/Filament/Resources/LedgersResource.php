<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Accounting\OAMInvoice;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LedgersResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LedgersResource\RelationManagers;

class LedgersResource extends Resource
{
    protected static ?string $model = OAMInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Ledgers';
    protected static ?string $navigationGroup = 'oam';

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
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->default("NA")
                    ->label('Invoice Number'),
                TextColumn::make('invoice_date')
                    ->date(),
                TextColumn::make('invoice_due_date')
                    ->date(),
                TextColumn::make('due_amount')
                    ->searchable()
                    ->default("NA")
                    ->label('Due Amount'),
                TextColumn::make('invoice_pdf_link')
                    ->label('Invoice Pdf Link'),
                TextColumn::make('amount_paid')
                    ->label('Credit'),
                TextColumn::make('invoice_amount')
                    ->label('Debit'),

            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLedgers::route('/'),
            'create' => Pages\CreateLedgers::route('/create'),
            'edit' => Pages\EditLedgers::route('/{record}/edit'),
        ];
    }
}
