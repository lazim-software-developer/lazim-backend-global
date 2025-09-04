<?php

namespace App\Filament\Resources\Building\FlatResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\RelationManagers\RelationManager;

use App\Models\Accounting\OAMInvoice;
use Filament\Notifications\Notification;
use App\Http\Controllers\User\PaymentController;


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
                ->limit(20)
		->copyable()
    		->copyMessage('Link copied!')
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
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
//                    ->url(function (OAMInvoice $record) {
//                        try {
//                            $controller = app(PaymentController::class);
//                            $response = $controller->fetchServiceChargePDF($record);
//                            return $response['url'];
//                        } catch (\Exception $e) {
//                            Notification::make()
//                                ->title('Error')
//                                ->body('Failed to download PDF: ' . $e->getMessage())
//                                ->danger()
//                                ->send();
//                            return null; // Prevent action from proceeding
//                        }
//                    })
//                    ->openUrlInNewTab()
                    ->color('success'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
