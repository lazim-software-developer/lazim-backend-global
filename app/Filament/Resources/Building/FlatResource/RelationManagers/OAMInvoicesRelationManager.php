<?php

namespace App\Filament\Resources\Building\FlatResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use App\Models\Accounting\OAMInvoice;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\User\PaymentController;
use Filament\Resources\RelationManagers\RelationManager;


class OAMInvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'oaminvoices';



    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_date')
                    ->sortable(),
                TextColumn::make('invoice_number')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('previous_balance')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('invoice_amount')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('invoice_due_date')
                    ->sortable(),
                TextColumn::make('invoice_period')
                    ->sortable(),
                // TextColumn::make('invoice_detail_link')
                //     ->limit(20),
                // TextColumn::make('invoice_pdf_link')
                //     ->limit(20),

                TextColumn::make('payment_url')
                    ->limit(20)
                    ->copyable()
                    ->copyMessage('Payment link copied')
                    ->copyMessageDuration(1500),
            ])
            ->defaultSort('invoice_date', 'desc')
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

                Tables\Actions\Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (OAMInvoice $record) {
                        try {
                            $controller = app(PaymentController::class);
                            $response = $controller->fetchServiceChargePDF($record);
                            return redirect($response['url']);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Failed to download PDF: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
