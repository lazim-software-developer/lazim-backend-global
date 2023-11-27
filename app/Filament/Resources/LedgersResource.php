<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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
use Filament\Tables\Actions\SelectAction;
use Filament\Tables\Filters\Filter;

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
                TextColumn::make('invoice_date')
                    ->date(),
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
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('invoice_quarter')
                    ->searchable()
                    ->label('Description'),
                TextColumn::make('invoice_due_date')
                    ->date(),
                TextColumn::make('invoice_pdf_link')
                    ->limit(20)
                    ->label('Invoice Pdf Link'),
                TextColumn::make('invoice_amount')
                    ->label('Debit'),
                TextColumn::make('amount_paid')
                    ->label('Credit'),
                TextColumn::make('due_amount')
                    ->searchable()
                    ->default("NA")
                    ->label('Balance'),
                    
                    ])
                    ->filters([
                        Filter::make('invoice_date')
                                ->form([
                                    DatePicker::make('from'),
                                    DatePicker::make('until'),
                                ])
                                ->query(function (Builder $query, array $data): Builder {
                                    return $query
                                        ->when(
                                            $data['from'],
                                            fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '>=', $date),
                                        )
                                        ->when(
                                            $data['until'],
                                            fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '<=', $date),
                                        );
                                    }),
                        Filter::make('type')
                                    ->form([
                                        Select::make('invoice_type')
                                        ->options([
                                            "service_charge" => "Service Charges Ledger",
                                            "cooling_accounts" => "Cooling Accounts",
                                            "other_income" => "Other Income",
                                            "general_fund_amount" => "General Fund Amount",
                                            "reserve_fund_amount" => "Reserve Fund Amount",
                                        ])
                                    ])
                                    ->query(function (Builder $query, array $data): Builder {
                                        return $query
                                            ->when(
                                                $data['invoice_type'],
                                                fn (Builder $query, $type): Builder => $query->where('type', $type),
                                            );
                                        })
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
            // 'create' => Pages\CreateLedgers::route('/create'),
            // 'edit' => Pages\EditLedgers::route('/{record}/edit'),
        ];
    }
}
