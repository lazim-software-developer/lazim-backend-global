<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerAssociationReceiptResource\Pages;
use App\Filament\Resources\OwnerAssociationReceiptResource\RelationManagers;
use App\Models\OwnerAssociationReceipt;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OwnerAssociationReceiptResource extends Resource
{
    protected static ?string $model = OwnerAssociationReceipt::class;

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
        return $table->modifyQueryUsing(fn (Builder $query) => $query->orderByDesc('created_at'))
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->form([
                    FileUpload::make('receipt_document')->disk('s3'),
                ])->visible(
                    function( OwnerAssociationReceipt $record){
                        if($record->receipt_document){
                            return true;
                        }
                        return false;
                    }),
                // Tables\Actions\EditAction::make(),
                Action::make('download')->url(function( OwnerAssociationReceipt $record){
                    return route('receipt',['data' => $record]);
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
            'index' => Pages\ListOwnerAssociationReceipts::route('/'),
            // 'create' => Pages\CreateOwnerAssociationReceipt::route('/create'),
            // 'view' => Pages\ViewOwnerAssociationReceipt::route('/{record}'),
            // 'edit' => Pages\EditOwnerAssociationReceipt::route('/{record}/edit'),
        ];
    }    
}
