<?php

namespace App\Filament\Resources;

use App\Models\Accounting\Invoice;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\Accounting\OAMInvoice;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LedgersResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LedgersResource\RelationManagers;
use App\Models\Building\Building;
use Filament\Forms\Components\Section;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Tables\Actions\SelectAction;
use Filament\Tables\Filters\Filter;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Filament\Tables\Actions\Action;

class LedgersResource extends Resource
{
    protected static ?string $model = OAMInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel = 'Service Charge Ledgers';
    protected static ?string $navigationGroup = 'Ledgers';

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
                    ->label('Date')
                    ->date(),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('flat.property_number')
                    ->searchable()
                    ->default('NA')
                    ->label('Unit Number')
                    ->limit(50),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->default("NA")
                    ->label('Invoice Number'),
                TextColumn::make('invoice_quarter')
                    ->searchable()
                    ->label('Description'),
                TextColumn::make('invoice_due_date')
                    ->date(),
                TextColumn::make('invoice_pdf_link')
                    ->limit(20)
                    ->label('Invoice Pdf Link'),
                TextColumn::make('invoice_amount')
                    ->label('Bill'),
                ViewColumn::make('Paid Amount')->view('tables.columns.invoice-amount-paid'),
                TextColumn::make('due_amount')
                    ->searchable()
                    ->default("NA")
                    ->label('Balance'),
                    
                    ])
                    ->defaultSort('created_at', 'desc')
                    ->filters([
                        Filter::make('invoice_date')
                            ->form([
                                DateRangePicker::make('Date')
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                if (isset($data['Date'])) {
                                    $dateRange = explode(' - ', $data['Date']);
                            
                                    if (count($dateRange) === 2) {
                                        $from = \Carbon\Carbon::createFromFormat('d/m/Y', $dateRange[0])->format('Y-m-d');
                                        $until = \Carbon\Carbon::createFromFormat('d/m/Y', $dateRange[1])->format('Y-m-d');
                            
                                        return $query
                                            ->when(
                                                $from,
                                                fn (Builder $query, $date) => $query->whereDate('invoice_date', '>=', $date)
                                            )
                                            ->when(
                                                $until,
                                                fn (Builder $query, $date) => $query->whereDate('invoice_date', '<=', $date)
                                            );
                                    }
                                }
                                
                                    return $query;
                                }),
                        Filter::make('Building')
                            ->form([
                                Select::make('building')
                                ->searchable()
                                ->options(function () {
                                    $oaId = auth()->user()->owner_association_id;
                                    return Building::where('owner_association_id', $oaId)
                                        ->pluck('name', 'id');
                                })
                            ])
                            ->query(function (Builder $query, array $data): Builder {
                                return $query
                                    ->when(
                                        $data['building'],
                                        fn (Builder $query, $building_id): Builder => $query->where('building_id', $building_id),
                                    );
                                }),
                            ],layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\ViewAction::make(),
                Action::make('View Receipts')
                    ->label('View Receipts')
                    ->url(function (OAMInvoice $record) {
                        return url('/admin/' . $record->id . '/receipts');
                    })
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
            // 'view' => Pages\ViewLedgers::route('/{record}'),
        ];
    }
}
