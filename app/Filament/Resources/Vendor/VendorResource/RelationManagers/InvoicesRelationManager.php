<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use App\Jobs\InvoiceRejectionJob;
use App\Models\AccountCredentials;
use App\Models\Accounting\Invoice;
use App\Models\InvoiceApproval;
use App\Models\Master\Role;
use App\Models\User\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('building_id')
                            ->relationship('building', 'name')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Building Name'),
                        Select::make('contract_id')
                            ->relationship('contract', 'contract_type')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Contract Type'),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Vendor Name'),
                        TextInput::make('invoice_number')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        Select::make('wda_id')
                            ->relationship('wda', 'job_description')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Job Description(WDA)'),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->disabled()
                            ->label('Start Date'),
                        FileUpload::make('document')
                            ->disk('s3')
                            ->directory('dev')
                            ->disabled()
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                            ]),
                        TextInput::make('opening_balance')
                            ->prefix('MVR')
                            ->readOnly()
                            ->live(),
                        TextInput::make('payment')
                            ->prefix('MVR')
                            ->numeric()
                            ->minValue(1)
                            // ->maxValue(function (Get $get) {
                            //     return $get('opening_balance') ?? $get('invoice_amount');
                            // })
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    return true;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    // $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    // return $invoiceapproval && Invoice::where('id', $record->id)->first()?->opening_balance == 0;
                                    return true;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    return true;
                                }
                            })
                            ->required(function (Invoice $record, Get $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    return false;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    return false; // true && $get('status') == 'approved'
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    return false;
                                }
                            })
                            // ->rules([function (Get $get) {
                            //     return function (string $attribute, $value, Closure $fail) use($get) {
                            //         if ($get('status')==='rejected' && $value) {
                            //             $fail('No need to input a payment amount when rejecting');
                            //         }
                            //     };
                            // },])
                            ->live(),
                        TextInput::make('balance')
                            ->prefix('MVR')
                            ->readOnly()
                            ->live(),
                        TextInput::make('invoice_amount')
                            ->label('Invoice Amount')
                            ->disabled()
                            ->prefix('MVR'),
                        Select::make('status')
                            ->required()
                            ->options([
                                'approved' => 'Approve',
                                'rejected' => 'Reject',
                            ])
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['OA', 'Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval; // && Invoice::where('id', $record->id)->first()?->opening_balance == 0
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                            })
                            ->searchable()
                            ->live(),
                        TextInput::make('remarks')
                            ->rules(['max:155'])
                            ->visible(function (callable $get) {
                                if ($get('status') == 'rejected') {
                                    return true;
                                }
                                return false;
                            })
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['OA', 'Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval; //&& Invoice::where('id', $record->id)->first()?->opening_balance == 0
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)->whereIn('role_id', Role::whereIn('name', ['MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                            })
                            ->live()
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->label('Building'),
                TextColumn::make('contract.contract_type')
                    ->label('Contract Type'),
                TextColumn::make('invoice_number')
                    ->label('Invoice Number'),
                TextColumn::make('wda.job_description')
                    ->label('Job Description(WDA)'),
                TextColumn::make('date')
                    ->default('NA')
                    ->label('Start Date'),
                TextColumn::make('status')
                    ->default('NA')
                    ->label('Status'),
                TextColumn::make('user.first_name')
                    ->default('NA')
                    ->label('Status updated by'),
                TextColumn::make('invoice_amount')
                    ->default('NA')
                    ->label('Invoice Amount'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function (array $data, $record) {
                        $connection = DB::connection('lazim_accounts');
                        $bill       = $connection->table('bills')->where('lazim_invoice_id', $record->id)->first();

                        $tenant = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
                        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');
                        $credentials     = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
                        $mailCredentials = [
                            'mail_host'         => $credentials->host ?? env('MAIL_HOST'),
                            'mail_port'         => $credentials->port ?? env('MAIL_PORT'),
                            'mail_username'     => $credentials->username ?? env('MAIL_USERNAME'),
                            'mail_password'     => $credentials->password ?? env('MAIL_PASSWORD'),
                            'mail_encryption'   => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
                        ];
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA' && !InvoiceApproval::where('invoice_id', $record->id)->where('active', true)->exists()) {

                            if ($record->status == 'approved') {
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status'     => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks'    => 'approved by oa',
                                    'active'     => true,
                                ]);
                            } else {
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status'     => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks'    => $record->remarks,
                                    'active'     => true,
                                ]);
                                // $connection->table('bills')->where('id', $bill->id)->update(['deleted_at' => now()]);
                                $user    = User::find($record->created_by);
                                $invoice = Invoice::find($record->id);
                                InvoiceRejectionJob::dispatch($user, $record->remarks, $invoice, $mailCredentials);
                            }
                        }
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                            // if ($record->opening_balance == null && is_numeric($record->invoice_amount) && is_numeric($record->payment)) {
                            //     Invoice::where('id', $record->id)
                            //         ->update([
                            //             'status_updated_by' => auth()->user()->id,
                            //             'opening_balance'   => $record->invoice_amount - $record->payment,
                            //             'balance'           => $record->invoice_amount - $record->payment,
                            //         ]);
                            // }
                            // if( is_numeric($record->opening_balance) && $record->opening_balance != null && is_numeric($record->payment)) {
                            //     Invoice::where('id', $record->id)
                            //         ->update([
                            //             'status_updated_by' => auth()->user()->id,
                            //             'opening_balance'   => $record->opening_balance - $record->payment,
                            //             'balance'           => $record->opening_balance - $record->payment,
                            //         ]);
                            //     $mdRecordExist = InvoiceApproval::where(['invoice_id' => $record->id, 'remarks' => 'approved by md', 'active' => true]);
                            //     if ($mdRecordExist->first()) {
                            //         $mdRecordExist->update(['active' => false]);
                            //     }

                            // }
                            if ($record->status == 'approved') {
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status'     => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks'    => 'approved by Account Manager',
                                    'active'     => true,
                                ]);

                                // if ($record->payment != null) {
                                //     $connection->table('bill_payments')->insert([
                                //         'bill_id'     => $bill?->id,
                                //         'date'        => now()->format('Y-m-d'),
                                //         'amount'      => $record->payment,
                                //         'account_id'  => 1,
                                //         'created_at'  => now(),
                                //         'updated_at'  => now(),
                                //         'building_id' => $bill?->building_id,

                                //     ]);
                                //     $connection->table('bills')->where('lazim_invoice_id', $record->id)->update([
                                //         'status' => Invoice::where('id', $record->id)->first()?->opening_balance == 0 ? 4 : 3,
                                //     ]);
                                //     $connection->table('transactions')->insert([
                                //         'user_id'     => $bill?->vender_id,
                                //         'user_type'   => 'vender',
                                //         'account'     => 1,
                                //         'type'        => 'payment',
                                //         'amount'      => $record->payment,
                                //         'date'        => now()->format('Y-m-d'),
                                //         'created_by'  => $bill->created_by,
                                //         'payment_id'  => $connection->table('bill_payments')->where('bill_id', $bill?->id)->latest()->first()?->id,
                                //         'category'    => 'bill',
                                //         'building_id' => $bill?->building_id,

                                //     ]);
                                // }

                            } else {
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status'     => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks'    => $record->remarks,
                                    'active'     => true,
                                ]);
                                // $connection->table('transactions')->whereIn('payment_id', $connection->table('bill_payments')->where('bill_id', $bill->id)->pluck('id'))->update(['deleted_at' => now()]);
                                // $connection->table('bill_payments')->where('bill_id', $bill->id)->update(['deleted_at' => now()]);
                                // $connection->table('bills')->where('id', $bill->id)->update(['deleted_at' => now()]);

                                $notify = User::where(['owner_association_id' => auth()->user()?->owner_association_id, 'role_id' => Role::where('name', 'OA')->first()->id])->first();
                                Notification::make()
                                    ->success()
                                    ->title("Invoice Rejection")
                                    ->icon('heroicon-o-document-text')
                                    ->iconColor('warning')
                                    ->body('We regret to inform that invoice ' . $record->invoice_number . ' has been rejected by Account Manager ' . auth()->user()->first_name . '.')
                                    ->type('invoice')
                                    ->priority('Low')
                                    ->sendToDatabase($notify);
                                $user    = User::find($record->created_by);
                                $invoice = Invoice::find($record->id);
                                InvoiceRejectionJob::dispatch($user, $record->remarks, $invoice, $mailCredentials);
                            }
                        }
                        if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                            if ($record->status == 'approved') {
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status'     => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks'    => 'approved by md',
                                    'active'     => true,
                                ]);


                                $product_services = $connection->table('product_services')
                                    ->where(['name' => $record->contract->service->name, 'building_id' => $record->contract->building_id])
                                    ->first();
                                $category = $connection->table('product_service_categories')
                                    ->where(['name' => $record->contract->service->subcategory->name, 'building_id' => $record->contract->building_id])->first();

                                if ($connection->table('bills')->where('lazim_invoice_id', $record->id)->count() == 0) {
                                    $creator     = $connection->table('users')->where(['type' => 'building', 'building_id' => $record->contract->building_id])->first();
                                    $httpRequest = Http::withOptions(['verify' => false])
                                        ->withHeaders([
                                            'Content-Type' => 'application/json',
                                        ])->post(env('ACCOUNTING_CREATE_BILL_API', 'http://localhost:8000/api/bill/create'), [
                                            'created_by'     => $creator->id,
                                            'buildingId'     => $record->contract->building_id,
                                            'invoiceId'      => $record->id,
                                            'venderId'       => $connection->table('venders')->where(['lazim_vendor_id' => $record->vendor_id, 'building_id' => $record->contract->building_id])->first()?->id,
                                            'billDate'       => $record->date,
                                            'dueDate'        => Carbon::parse($record->date)->addDays(30),
                                            'categoryId'     => $category?->id,
                                            'chartAccountId' => null,
                                            'items'          => [
                                                [
                                                    'item'             => $product_services?->id,
                                                    'quantity'         => 1,
                                                    'tax'              => $connection->table('taxes')->where(['building_id' => $record->contract->building_id, 'name' => 'VAT'])->first()->id,
                                                    'price'            => $record->invoice_amount / (1 + 5 / 100),
                                                    'chart_account_id' => $product_services->expense_chartaccount_id,
                                                ],
                                            ],
                                        ]);
                                }
                            } else {
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status'     => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks'    => $record->remarks,
                                    'active'     => true,
                                ]);
                                // $connection->table('transactions')->whereIn('payment_id', $connection->table('bill_payments')->where('bill_id', $bill->id)->pluck('id'))->update(['deleted_at' => now()]);
                                // $connection->table('bill_payments')->where('bill_id', $bill->id)->update(['deleted_at' => now()]);
                                // $connection->table('bills')->where('id', $bill->id)->update(['deleted_at' => now()]);
                                $notifyoa  = User::where(['owner_association_id' => auth()->user()?->owner_association_id, 'role_id' => Role::where('name', 'OA')->first()->id])->first();
                                $notifyacc = User::where(['owner_association_id' => auth()->user()?->owner_association_id, 'role_id' => Role::where('name', 'Accounts Manager')->first()->id])->get();
                                // dd($notifyacc);
                                Notification::make()
                                    ->success()
                                    ->title("Invoice Rejection")
                                    ->icon('heroicon-o-document-text')
                                    ->iconColor('warning')
                                    ->body('We regret to inform that invoice ' . $record->invoice_number . ' has been rejected by MD ' . auth()->user()->first_name . '.')
                                    ->type('invoice')
                                    ->priority('Low')
                                    ->sendToDatabase($notifyoa);
                                foreach ($notifyacc as $user) {
                                    Notification::make()
                                        ->success()
                                        ->title("Invoice Rejection")
                                        ->icon('heroicon-o-document-text')
                                        ->iconColor('warning')
                                        ->body('We regret to inform that invoice ' . $record->invoice_number . ' has been rejected by MD ' . auth()->user()->first_name . '.')
                                        ->type('invoice')
                                        ->priority('Low')
                                        ->sendToDatabase($user);
                                }

                                $user    = User::find($record->created_by);
                                $invoice = Invoice::find($record->id);
                                InvoiceRejectionJob::dispatch($user, $record->remarks, $invoice, $mailCredentials);
                            }
                        }
                    })
                    ->mutateRecordDataUsing(function (array $data): array {
                        if ($data['status'] == 'pending') {
                            $data['status'] = null;
                        }
                        $data['remarks'] = null;
                        // $data['payment'] = null;
                        return $data;
                    }),
                //Tables\Actions\DeleteAction::make(),
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
}
