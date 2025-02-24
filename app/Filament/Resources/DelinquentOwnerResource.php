<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DelinquentOwnerResource\Pages;
use App\Jobs\OAM\InvoiceDueMailJob;
use App\Models\AccountCredentials;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\DelinquentOwner;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
                TextColumn::make('flat.property_number')->label('Unit')->searchable(),
                TextColumn::make('owner.name')->limit(25),
                TextColumn::make('last_payment_date')->default('NA'),
                TextColumn::make('last_payment_amount')->default('NA'),
                TextColumn::make('outstanding_balance'),
                TextColumn::make('quarter_1_balance')->default('NA'),
                TextColumn::make('quarter_2_balance')->default('NA'),
                TextColumn::make('quarter_3_balance')->default('NA'),
                TextColumn::make('quarter_4_balance')->default('NA'),
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
                Filter::make('invoice_date')
                    ->form([
                        Select::make('year')
                            ->searchable()
                            ->placeholder('Select Year')
                            ->options(array_combine(range(now()->year, 2018), range(now()->year, 2018))),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (isset($data['year'])) {
                            return $query
                                ->when(
                                    $data['year'],
                                    fn(Builder $query, $year) => $query->where('year', $year)
                                );
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
            'index' => Pages\ListDelinquentOwners::route('/'),
            // 'create' => Pages\CreateDelinquentOwner::route('/create'),
            // 'edit' => Pages\EditDelinquentOwner::route('/{record}/edit'),
        ];
    }
}
