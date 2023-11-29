<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\VendorLedgers;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\Accounting\Invoice;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VendorLedgersResource\Pages;
use App\Filament\Resources\VendorLedgersResource\RelationManagers;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class VendorLedgersResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Service Provider Ledgers';

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
                TextColumn::make('date')
                    ->date(),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->default("NA")
                    ->label('Invoice Number'),
                ViewColumn::make('Code')->view('tables.columns.vendorledgerscode'),
                TextColumn::make('vendor.name')
                    ->searchable()
                    ->label('Vendor Name'),
                TextInputColumn::make('opening_balance')->label('Opening Balance'),
                ImageColumn::make('document')
                    ->square()
                    ->label('Invoice PDF'),
                TextColumn::make('invoice_amount')
                    ->label('Bill Amount'),
                TextInputColumn::make('payment')->label('Payment'),
                TextInputColumn::make('balance')->label('Balance'),
                TextColumn::make('status')
                    ->searchable(),
            ])
            ->filters([
                Filter::make('date')
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
                                        fn(Builder $query, $date) => $query->whereDate('date', '>=', $date)
                                    )
                                    ->when(
                                        $until,
                                        fn(Builder $query, $date) => $query->whereDate('date', '<=', $date)
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
                                fn(Builder $query, $building_id): Builder => $query->where('building_id', $building_id),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('Update Status')
                    ->visible(fn($record) => $record->status == null)
                    ->button()
                    ->form([
                        Select::make('status')
                            ->options([
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                            ])
                            ->searchable()
                            ->live(),
                        TextInput::make('comment')
                            ->rules(['max:255'])
                            ->required(),
                    ])
                    ->fillForm(fn(Invoice $record): array => [
                        'status' => $record->status,
                        'comment' => $record->remarks,
                    ])
                    ->action(function (Invoice $record, array $data): void {
                        
                            $record->status = $data['status'];
                            $record->remarks = $data['comment'];
                            $record->save();
                            DB::table('invoice_status')->insert([
                                'invoice_id' => $record->id,
                                'status' => $data['status'],
                                'updated_by' => auth()->user()->id,
                                'comment' => $data['comment'],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                    })
                    ->slideOver()
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
            'index' => Pages\ListVendorLedgers::route('/'),
            // 'create' => Pages\CreateVendorLedgers::route('/create'),
            // 'edit' => Pages\EditVendorLedgers::route('/{record}/edit'),
        ];
    }
}
