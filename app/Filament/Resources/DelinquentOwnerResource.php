<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\ApartmentOwner;
use Filament\Facades\Filament;
use App\Models\DelinquentOwner;
use Filament\Resources\Resource;
use App\Models\Building\Building;
use App\Models\AccountCredentials;
use App\Jobs\OAM\InvoiceDueMailJob;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\User\OwnerResource;
use App\Filament\Resources\DelinquentOwnerResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class DelinquentOwnerResource extends Resource
{
    protected static ?string $model = DelinquentOwner::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $modelLabel     = 'DelinquentOwners';

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
                TextColumn::make('flat.property_number')->label('Unit')->sortable()->searchable(),
                TextColumn::make('outstanding_balance')->sortable(),
                TextColumn::make('owner.name')
                    ->label('Owners')
                    ->html()
                    ->getStateUsing(function ($record) {
                        $owners = $record->flat->owners;
                        return $owners->isEmpty()
                            ? 'No owners'
                            : $owners->pluck('name')->filter()->join(', ');
                    })
                    ->formatStateUsing(function ($record) {
                        $owners = $record->flat->owners; // Adjust relationship
                        if ($owners->isEmpty()) {
                            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">No owners</span>';
                        }
                        $html = '<div class="flex flex-col gap-1">';
                        foreach ($owners as $owner) {
                            $name = $owner->name ?? 'N/A';
                            $url = class_exists(OwnerResource::class) ? OwnerResource::getUrl('view', ['record' => $owner->id]): route('filament.resources.apartment-owners.view', ['record' => $owner->id]);
                            // Ensure the URL is valid
                            $url = !filter_var($url, FILTER_VALIDATE_URL) ? '#' : $url; // Fallback to a safe URL if the generated one is invalid
                            $html .= '<a href="' . $url . '" target="_blank" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 mr-1">' . e($name) . '</a>';
                        }
                        $html .= '</div>';
                        return $html;
                    })
                    ->sortable(false),
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
                    ->sortable(false),
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
                // TextColumn::make('last_payment_date')
                // ->label('Last Payment Date')
                // ->dateTime('Y-m-d')
                // ->default('NA'),
                // TextColumn::make('last_payment_amount')->default('NA'),
                // TextColumn::make('quarter_1_balance')->default('NA'),
                // TextColumn::make('quarter_2_balance')->default('NA'),
                // TextColumn::make('quarter_3_balance')->default('NA'),
                // TextColumn::make('quarter_4_balance')->default('NA'),
                // TextColumn::make('invoice_pdf_link')->label('invoice_file')->formatStateUsing(fn ($state) => '<a href="' . $state . '" target="_blank">LINK</a>')
                // ->html(),
                TextColumn::make('invoice_pdf_link')
                    ->label('Invoice File')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->outstanding_balance == 0) {
                            return 'NA';
                        }

                        return '<a href="' . $state . '" target="_blank">LINK</a>';
                    })
                    ->html(),

            ])
            ->filters([
                // Filter::make('invoice_date')
                //     ->form([
                //         Select::make('year')
                //             ->searchable()
                //             ->placeholder('Select Year')
                //             ->options(array_combine(range(now()->year, 2018), range(now()->year, 2018))),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         if (isset($data['year'])) {
                //             return $query
                //                 ->when(
                //                     $data['year'],
                //                     fn(Builder $query, $year) => $query->where('year', $year)
                //                 );
                //         }

                //         return $query;
                //     }),
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
                                fn(Builder $query, $building_id): Builder => $query->where('building_id', $building_id)
                                            ->where('outstanding_balance', '>', 0)
                                            ->where('year', now()->year)
                                            ->orderBy('id', 'desc'),
                            );
                    }),
            ], layout: FiltersLayout::AboveContent)->filtersFormColumns(3)
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('Remind')
                        ->form([
                            Textarea::make('content')
                                ->maxLength(500)
                                ->rows(10)
                                ->label('Content of email')
                                ->helperText('Enter content with values less than 500 characters.'),
                        ])
                        ->fillForm(fn(DelinquentOwner $record): array=> [
                            'content' => 'Your payment is Due, please make the payment ASAP.',
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $tenant = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                            // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');

                            $credentials     = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
                            $mailCredentials = [
                                'mail_host'         => $credentials->host ?? env('MAIL_HOST'),
                                'mail_port'         => $credentials->port ?? env('MAIL_PORT'),
                                'mail_username'     => $credentials->username ?? env('MAIL_USERNAME'),
                                'mail_password'     => $credentials->password ?? env('MAIL_PASSWORD'),
                                'mail_encryption'   => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                                'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
                            ];

                            foreach ($records as $record) {
                                // Access the owner_id of each selected record

                                $owner   = ApartmentOwner::find($record->owner_id);
                                $content = $data['content'];
                                InvoiceDueMailJob::dispatch($owner, $content, $mailCredentials);
                            }
                            Notification::make()
                                ->title("Notification: Outstanding Balance")
                                ->success()
                                ->body("Reminder sent regarding Outstanding Balance.")
                                ->send();
                        })
                        ->slideOver(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->emptyStateHeading('No results found')
            ->emptyStateDescription('Please apply a filter to load building data.')
            ->defaultSort('id', 'desc')
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
            })
            ;
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
            'index' => Pages\ListDelinquentOwners::route('/'),
            // 'create' => Pages\CreateDelinquentOwner::route('/create'),
            // 'edit' => Pages\EditDelinquentOwner::route('/{record}/edit'),
        ];
    }
}
