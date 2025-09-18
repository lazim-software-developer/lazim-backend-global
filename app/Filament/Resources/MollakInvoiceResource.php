<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use App\Models\Accounting\OAMInvoice;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
// use App\Filament\Resources\User\OwnerResource;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\User\PaymentController;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MollakInvoiceResource\Pages;
use App\Filament\Resources\MollakInvoiceResource\RelationManagers;

class MollakInvoiceResource extends Resource
{
    protected static ?string $model = OAMInvoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Financial Reports';
    protected static ?string $modelLabel      = 'Invoices';
    protected static ?string $recordTitleAttribute = 'invoice_number';

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
                TextColumn::make('flat.property_number')
                    ->label('Unit')
                    ->sortable()
                    ->searchable(),
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
                // TextColumn::make('owner.name')
                //     ->label('Owners')
                //     ->html()
                //     ->getStateUsing(function ($record) {
                //         $owners = $record->flat->owners;
                //         return $owners->isEmpty()
                //             ? 'No owners'
                //             : $owners->pluck('name')->filter()->join(', ');
                //     })
                //     ->formatStateUsing(function ($record) {
                //         $owners = $record->flat->owners; // Adjust relationship
                //         if ($owners->isEmpty()) {
                //             return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No owners</span>';
                //         }
                //         $html = '<div class="flex flex-col gap-1">';
                //         foreach ($owners as $owner) {
                //             $name = $owner->name ?? 'N/A';
                //             $url = class_exists(OwnerResource::class) ? OwnerResource::getUrl('view', ['record' => $owner->id]): route('filament.resources.apartment-owners.view', ['record' => $owner->id]);
                //             // Ensure the URL is valid
                //             $url = !filter_var($url, FILTER_VALIDATE_URL) ? '#' : $url; // Fallback to a safe URL if the generated one is invalid
                //             $html .= '<a href="' . $url . '" target="_blank" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 mr-1">' . e($name) . '</a>';
                //         }
                //         $html .= '</div>';
                //         return $html;
                //     })
                //     ->sortable(false),
                // TextColumn::make('owner.name')->sortable()->limit(25),

                TextColumn::make('owner.mobile')
                    ->label('Contact')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $owners = $record->flat->owners;
                        return $owners->isEmpty()
                            ? 'No owners'
                            : $owners->pluck('mobile')->filter()->join(', ');
                    })
                    ->formatStateUsing(function ($record) {
                        $owners = $record->flat->owners; // Adjust relationship
                        if ($owners->isEmpty()) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No contact</span>';
                        }
                        $html = '<div class="flex flex-col gap-1">';
                        foreach ($owners as $owner) {
                            $moblie = $owner->mobile ?? 'N/A';
                            $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 mr-1">' . e($moblie) . '</span>';
                        }
                        $html .= '</div>';
                        return $html;
                    })
                    ->sortable(false)
                    ->toggleable(),
                TextColumn::make('owner.email')
                    ->label('Email')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $owners = $record->flat->owners;
                        return $owners->isEmpty()
                            ? 'No owners'
                            : $owners->pluck('email')->filter()->join(', ');
                    })
                    ->formatStateUsing(function ($record) {
                        $owners = $record->flat->owners; // Adjust relationship
                        if ($owners->isEmpty()) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No contact</span>';
                        }
                        $html = '<div class="flex flex-col gap-1">';
                        foreach ($owners as $owner) {
                            $email = $owner->email ?? 'N/A';
                            $html .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 mr-1">' . e($email) . '</span>';
                        }
                        $html .= '</div>';
                        return $html;
                    })
                    ->sortable(false),
                // TextColumn::make('invoice_pdf_link')
                //     ->label('Invoice File')
                //     ->formatStateUsing(function ($state, $record) {
                //         // dd($state, $record);
                //         return '<a href="' . ($record->pdf_path ?? '#') . '" target="_blank">Download</a>';
                //     })
                //     ->html(),

            ])
            ->searchOnBlur()
            ->filters([
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
                        return $query->when($data['building'], fn(Builder $query, $building_id): Builder => $query->where('building_id', $building_id))
                            ->orderBy('id', 'desc');
                    })
                    ->indicateUsing(function (Builder $query, array $data): array {
                        $indicators = [];
                        if ($data['building'] ?? null) {
                            $buildingName = Building::find($data['building'])?->name ?? 'Selected Building';
                            $indicators[] = Indicator::make($buildingName)
                                ->removeField('building');
                        }
                        return $indicators;
                    }),
                Filter::make('invoice_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('From Date')
                            ->maxDate(fn(\Filament\Forms\Get $get) => $get('until') ?: now()),
                        DatePicker::make('until')
                            ->label('Until Date')
                            ->minDate(fn(\Filament\Forms\Get $get) => $get('from'))
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn(Builder $query, $from) => $query->whereDate('invoice_date', '>=', $from))
                            ->when($data['until'], fn(Builder $query, $until) => $query->whereDate('invoice_date', '<=', $until))
                            ->orderBy('id', 'desc');
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make('From: ' . Carbon::parse($data['from'])->toFormattedDateString())
                                ->removeField('from');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make('Until: ' . Carbon::parse($data['until'])->toFormattedDateString())
                                ->removeField('until');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                // Add a Download PDF action to the table
                // Tables\Actions\EditAction::make(),
                Action::make('download_pdf')
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
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([])
            ->emptyStateHeading('No results found')
            ->emptyStateDescription('Please apply a filter to load building data.')
            ->defaultSort('invoice_date', 'desc')
            ->modifyQueryUsing(function (Builder $query, Table $table) {
                // Check if any filters are applied
                $activeFilters = collect($table->getFilters())->filter(function ($filter) {
                    return $filter->getState() && !empty(array_filter($filter->getState()));
                });

                // If no filters are applied, return an empty query
                if ($activeFilters->isEmpty()) {
                    return $query->whereRaw('1 = 0'); // Returns no results
                }

                // Apply normal query logic if filters are active
                return $query;
            });
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
            'index' => Pages\ListMollakInvoices::route('/'),
            // 'view' => Pages\ViewMollakInvoice::route('/{record}'),
            // 'edit' => Pages\EditMollakInvoice::route('/{record}/edit'),
        ];
    }
    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->withoutGlobalScopes([
    //             // SoftDeletingScope::class,
    //         ]);
    // }
}
