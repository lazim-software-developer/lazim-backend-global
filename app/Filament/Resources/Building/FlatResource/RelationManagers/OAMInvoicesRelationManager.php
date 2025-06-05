<?php

namespace App\Filament\Resources\Building\FlatResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;


class OAMInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'oaminvoices';



    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_date'),
                TextColumn::make('invoice_number')
                    ->searchable(),
                TextColumn::make('previous_balance')
                    ->searchable(),
                TextColumn::make('invoice_amount')
                    ->searchable(),
                TextColumn::make('invoice_due_date'),
                TextColumn::make('invoice_period'),
                // TextColumn::make('invoice_detail_link')
                //     ->limit(20),
                // TextColumn::make('invoice_pdf_link')
                //     ->limit(20),
                TextColumn::make('payment_url')
                    ->limit(20),
            ])
            ->filters([

                Filter::make('invoice_date')
                    ->form([
                        DatePicker::make('invoice_date')
                            ->label('From Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['invoice_date'],
                            fn($query, $date) => $query->whereDate('invoice_date', '>=', $date)
                        );
                    }),

                Filter::make('invoice_due_date')
                    ->form([
                        DatePicker::make('invoice_due_date')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['invoice_due_date'],
                            fn($query, $date) => $query->whereDate('invoice_due_date', '<=', $date)
                        );
                    }),

            ])
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
