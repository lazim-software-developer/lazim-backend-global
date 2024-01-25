<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\InvoiceApproval;
use App\Jobs\InvoiceRejectionJob;
use App\Models\Accounting\Invoice;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
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
                                'md' => 1,
                                'lg' => 2,
                            ]),
                        TextInput::make('opening_balance')
                            ->prefix('AED')
                            ->disabled()
                            ->live(),
                        TextInput::make('payment')
                            ->prefix('AED')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(function(Get $get){
                                return $get('invoice_amount');
                            })
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    return true;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['Accounts Manager','MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    return true;
                                }
                            })
                            ->required(function (Invoice $record,Get $get) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    return false;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    return true && $get('status') == 'approved';
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    return false;
                                }
                            })
                            ->live(),
                        TextInput::make('balance')
                            ->prefix('AED')
                            ->disabled()
                            ->live(),
                        TextInput::make('invoice_amount')
                            ->label('Invoice Amount')
                            ->disabled()
                            ->prefix('AED'),
                        Select::make('status')
                            ->required()
                            ->options([
                                'approved' => 'Approve',
                                'rejected' => 'Reject',
                            ])
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['OA','Accounts Manager','MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['Accounts Manager','MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['MD'])->pluck('id'))->pluck('id'))->exists();
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
                                    $invoiceapproval = InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['OA','Accounts Manager','MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['Accounts Manager','MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                            })
                            ->live()
                            ->required(),
                    ])
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
                    ->after(function ($record) {
                        if(Role::where('id', auth()->user()->role_id)->first()->name == 'OA' && !InvoiceApproval::where('invoice_id',$record->id)->where('active',true)->exists()){
            
                            if($record->status == 'approved'){
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => 'approved by oa',
                                    'active' => true,
                                ]);
                            }
                            else{
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => $record->remarks,
                                    'active' => true,
                                ]);
                                $user = User::find($record->created_by);
                                $invoice = Invoice::find($record->id);
                                InvoiceRejectionJob::dispatch($user, $record->remarks, $invoice);
                            }
                            
                        }
                        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager'){
                            if($record->status == 'approved'){
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => 'approved by Account Manager',
                                    'active' => true,
                                ]);
                            }
                            else{
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => $record->remarks,
                                    'active' => true
                                ]);
                                $notify = User::where(['owner_association_id'=>auth()->user()->owner_association_id,'role_id'=>Role::where('name','OA')->first()->id])->first();
                                Notification::make()
                                        ->success()
                                        ->title("Invoice Rejection")
                                        ->icon('heroicon-o-document-text')
                                        ->iconColor('warning')
                                        ->body('We regret to inform that invoice '.$record->invoice_number.' has been rejected by Account Manager '.auth()->user()->first_name.'.')
                                        ->sendToDatabase($notify);
                                $user = User::find($record->created_by);
                                $invoice = Invoice::find($record->id);
                                InvoiceRejectionJob::dispatch($user, $record->remarks, $invoice);
                            }
                        }
                        if(Role::where('id', auth()->user()->role_id)->first()->name == 'MD'){
                            if($record->status == 'approved'){
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => 'approved by md',
                                    'active' => true,
                                ]);
                            }
                            else{
                                InvoiceApproval::firstOrCreate([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => $record->remarks,
                                    'active' => true,
                                ]);
                                $notifyoa = User::where(['owner_association_id'=>auth()->user()->owner_association_id,'role_id'=>Role::where('name','OA')->first()->id])->first();
                                $notifyacc = User::where(['owner_association_id'=>auth()->user()->owner_association_id,'role_id'=>Role::where('name','Accounts Manager')->first()->id])->get();
                                // dd($notifyacc);
                                Notification::make()
                                        ->success()
                                        ->title("Invoice Rejection")
                                        ->icon('heroicon-o-document-text')
                                        ->iconColor('warning')
                                        ->body('We regret to inform that invoice '.$record->invoice_number.' has been rejected by MD '.auth()->user()->first_name.'.')
                                        ->sendToDatabase($notifyoa);
                                foreach ($notifyacc as $user) {
                                            Notification::make()
                                            ->success()
                                            ->title("Invoice Rejection")
                                            ->icon('heroicon-o-document-text')
                                            ->iconColor('warning')
                                            ->body('We regret to inform that invoice '.$record->invoice_number.' has been rejected by MD '.auth()->user()->first_name.'.')
                                            ->sendToDatabase($user);
                                }
                                
                                $user = User::find($record->created_by);
                                $invoice = Invoice::find($record->id);
                                InvoiceRejectionJob::dispatch($user, $record->remarks, $invoice);
                            }
                        }
                        Invoice::where('id', $record->id)
                            ->update([
                                'status_updated_by' => auth()->user()->id,
                                'opening_balance' => $record->invoice_amount - $record->payment,
                                'balance' => $record->invoice_amount - $record->payment,
                            ]);
                    })
                    ->mutateRecordDataUsing(function (array $data): array {
                        if ($data['status'] == 'pending') {
                            $data['status'] = null;
                        }
                        return $data;
                    })
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
