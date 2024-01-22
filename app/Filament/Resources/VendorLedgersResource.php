<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\VendorLedgersResource\Pages;
use App\Filament\Resources\VendorLedgersResource\RelationManagers;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Illuminate\Support\Str;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class VendorLedgersResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Service provider ledgers';
    protected static ?string $title = 'Service provider ledgers';
    protected static ?string $navigationGroup = 'Ledgers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')->label('Start Date')->disabled(),
                Select::make('building_id')
                    ->relationship('building', 'name')
                    ->preload()
                    ->disabled()
                    ->searchable()
                    ->label('Building Name'),
                Select::make('vendor_id')
                    ->relationship('vendor', 'name')
                    ->preload()
                    ->disabled()
                    ->searchable()
                    ->label('vendor Name'),
                Select::make('status')
                    ->options([
                        'approved' => 'Approved',
                        'pending' => 'Pending',
                    ])
                    ->disabled()
                    ->searchable()
                    ->reactive()
                    ->live(),
                TextInput::make('invoice_number')->disabled(),
                TextInput::make('opening_balance')->prefix('AED')->disabled()->live(),
                TextInput::make('invoice_amount')->prefix('AED')->disabled()->live(),
                TextInput::make('payment')->prefix('AED')->numeric()->disabled()->minValue(1)->maxValue(1000000)->live(),
                TextInput::make('balance')->prefix('AED')->disabled()->live(),

                FileUpload::make('document')
                    ->disk('s3')
                    ->directory('dev')
                    ->openable(true)
                    ->downloadable(true),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Start Date')
                    ->date(),
                TextColumn::make('building.name')
                    ->searchable()
                    ->default('NA')
                    ->limit(50),
                ViewColumn::make('Code')->view('tables.columns.vendorledgerscode'),
                TextColumn::make('vendor.name')
                    ->searchable()
                    ->label('Vendor Name'),
                TextColumn::make('opening_balance')->label('Opening Balance'),
                TextColumn::make('invoice_amount')
                    ->label('Bill Amount'),
                TextColumn::make('payment')->label('Payment'),
                TextColumn::make('balance')->label('Balance'),
                TextColumn::make('status')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('date')
                    ->form([
                        Flatpickr::make('Date')
                            ->range(true),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['Date'])) {
                            $segments = Str::of($data['Date'])->split('/[\s,]+/');

                            if (count($segments) === 3) {
                                $from = $segments[0];
                                $until = $segments[2];

                                return $query->whereBetween('date', [$from, $until]);
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
                Tables\Actions\EditAction::make(),
                // Action::make('Update Payment')
                //     ->button()
                //     ->form([
                //         TextInput::make('invoice_amount')
                //             ->disabled()
                //             ->label('Bill Amount'),
                //         TextInput::make('payment'),
                //     ])
                //     ->fillForm(fn(Invoice $record): array => [
                //         'invoice_amount' => $record->invoice_amount,
                //         'payment' => $record->payment,
                //     ])
                //     ->action(function (Invoice $record, array $data): void {
                //         $record->payment = $data['payment'];
                //         $record->opening_balance = $record->invoice_amount - $data['payment'];
                //         $record->balance = $record->invoice_amount - $data['payment'];
                //         $record->save();
                //     })
                //     ->slideOver()
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
            //'view' => Pages\ViewVendorLedgers::route('/{record}'),
            'edit' => Pages\EditVendorLedgers::route('/{record}/edit'),
        ];
    }
}
