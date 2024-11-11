<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerAssociationInvoiceResource\Pages;
use App\Models\OwnerAssociationInvoice;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OwnerAssociationInvoiceResource extends Resource
{
    protected static ?string $model = OwnerAssociationInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Generate Invoice';

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
                TextColumn::make('description')->limit(50),
                TextColumn::make('quantity')->default('NA'),
                TextColumn::make('rate'),
                TextColumn::make('tax'),
                TextColumn::make('status')
                    ->badge()
                    ->default('NA')
                    ->visible(fn () => auth()->user()?->role->name == 'Property Manager')
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'overdue' => 'danger',
                        'NA' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('download')->url(function (OwnerAssociationInvoice $record) {
                    return route('invoice', ['data' => $record]);
                }),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (OwnerAssociationInvoice $record): string =>
                        "/app/owner-association-invoices/{$record->id}/edit"
                    )
                    ->visible(fn () => auth()->user()?->role->name == 'Property Manager'),

            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Invoices')
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
            'edit' => Pages\EditInvoiceStatus::route('/{record}/edit'),
        ];
    }
}
