<?php

namespace App\Filament\Resources\Building\FlatResource\RelationManagers;

use Carbon\Carbon;
use Date;
use DateTime;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;


class OAMReceiptsRelationManager extends RelationManager
{
    protected static string $relationship = 'oamreceipts';



    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receipt_date')
                    ->formatStateUsing(fn($record) => Carbon::parse($record->receipt_date)->format('d-m-Y H:i:s'))
                    ->sortable(),
                TextColumn::make('receipt_number')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('receipt_amount')
                    ->searchable(),
                TextColumn::make('receipt_period')
                    ->sortable(),
                TextColumn::make('payment_status'),
                TextColumn::make('id')
                    ->label('Transaction Reference')
                    ->searchable()
                    ->formatStateUsing(
                        fn($record) =>
                        isset($record->transaction_reference) && !empty($record->transaction_reference)
                            ?  $record->transaction_reference
                            : optional(json_decode($record->noqodi_info))->noqoodiReference
                    ),
                TextColumn::make('payment_mode'),
                TextColumn::make('noqodi_info')->label('Invoice number')->searchable()->default('NA')->formatStateUsing(fn($state) => json_decode($state) ? json_decode($state)->invoiceNumber : 'NA'),
                TextColumn::make('from_date')->label('General fund')->formatStateUsing(fn($record) => $record->payment_mode == "Virtual Account Transfer" ? $record->receipt_amount : ($record->noqodi_info ? number_format(json_decode($record->noqodi_info)->generalFundAmount, 2) : 0)),
                TextColumn::make('to_date')->label('Reserve fund')->formatStateUsing(fn($record) => $record->noqodi_info ? number_format(json_decode($record->noqodi_info)->reservedFundAmount, 2) : 0),
            ])
            ->filters([
                Filter::make('receipt_date')
                    ->form([
                        DatePicker::make('receipt_date')
                            ->label('Receipt Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['receipt_date'],
                            fn($query, $date) => $query->whereDate('receipt_date', '=', $date)
                        );
                    }),

            ])->defaultSort('receipt_date', 'desc')
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
