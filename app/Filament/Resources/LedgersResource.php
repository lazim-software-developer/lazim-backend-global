<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LedgersResource\Pages;
use App\Models\Accounting\OAMInvoice;
use App\Models\Building\Building;
use App\Models\Master\Role;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class LedgersResource extends Resource
{
    protected static ?string $model = OAMInvoice::class;

    protected static ?string $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel      = 'Service charge ledgers';
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
                    ->label('Unit number')
                    ->limit(50),
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->default("NA")
                    ->label('Invoice number'),
                TextColumn::make('invoice_quarter')
                    ->searchable()
                    ->label('Description'),
                TextColumn::make('invoice_due_date')
                    ->date(),
                TextColumn::make('invoice_pdf_link')
                    ->limit(20)
                    ->label('Invoice'),
                TextColumn::make('invoice_amount')->formatStateUsing(fn($state) => number_format($state, 2))
                    ->label('Invoice amount')->alignEnd(),
                ViewColumn::make('Paid Amount')->view('tables.columns.invoice-amount-paid')
                    ->alignEnd(),
                TextColumn::make('due_amount')
                    ->searchable()
                    ->default("NA")
                    ->alignEnd()
                    ->label('Due balance'),
                TextColumn::make('previous_balance')
                    ->searchable()
                    ->default("NA")
                    ->alignEnd()
                    ->label('Previous balance'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('invoice_date')
                    ->form([
                        Flatpickr::make('Date')
                            ->range(true),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['Date'])) {
                            $segments = Str::of($data['Date'])->split('/[\s,]+/');

                            if (count($segments) === 3) {
                                $from  = $segments[0];
                                $until = $segments[2];

                                return $query->whereBetween('invoice_date', [$from, $until]);
                            }
                        }
                        return $query;
                    }),
                Filter::make('Building')
                    ->form([
                        Select::make('building')
                            ->searchable()
                            ->options(function () {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Admin') {
                                    return Building::all()->pluck('name', 'id');
                                } else {
                                    return Building::where('owner_association_id', auth()->user()?->owner_association_id)
                                        ->pluck('name', 'id');
                                }
                            }),
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
                // Tables\Actions\ViewAction::make(),
                Action::make('View Receipts')
                    ->label('View Receipts')
                    ->url(function (OAMInvoice $record) {
                        return url('/admin/' . $record->id . '/receipts');
                    }),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
