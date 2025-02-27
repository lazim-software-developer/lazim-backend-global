<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerAssociationReceiptResource\Pages;
use App\Models\OwnerAssociationReceipt;
use EditStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OwnerAssociationReceiptResource extends Resource
{
    protected static ?string $model = OwnerAssociationReceipt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel     = 'Generate Receipt';

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
                TextColumn::make('receipt_number'),
                TextColumn::make('date'),
                TextColumn::make('paid_by'),
                TextColumn::make('type'),
                TextColumn::make('payment_method'),
                TextColumn::make('received_in'),
                TextColumn::make('payment_reference'),
                TextColumn::make('on_account_of'),
                TextColumn::make('amount'),
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
                Tables\Actions\ViewAction::make()
                    ->form([
                        FileUpload::make('receipt_document')->disk('s3'),
                    ])->visible(
                    function (OwnerAssociationReceipt $record) {
                        if ($record->receipt_document) {
                            return true;
                        }
                        return false;
                    }),
                // Tables\Actions\EditAction::make(),

                Action::make('download')->url(function (OwnerAssociationReceipt $record) {
                    return route('receipt', ['data' => $record]);
                }),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn(OwnerAssociationReceipt $record): string =>
                        "/app/owner-association-receipts/{$record->id}/edit")
                    ->visible(fn() => auth()->user()?->role->name == 'Property Manager'),
            ])
            ->emptyStateHeading('No Receipts')
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
            'index' => Pages\ListOwnerAssociationReceipts::route('/'),
            'edit' => Pages\EditStatus::route('/{record}/edit'),
            // 'create' => Pages\CreateOwnerAssociationReceipt::route('/create'),
            // 'view' => Pages\ViewOwnerAssociationReceipt::route('/{record}'),
            // 'edit' => Pages\EditOwnerAssociationReceipt::route('/{record}/edit'),
        ];
    }
}
