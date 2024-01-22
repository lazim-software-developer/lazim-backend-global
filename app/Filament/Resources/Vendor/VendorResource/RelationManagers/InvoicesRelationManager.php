<?php

namespace App\Filament\Resources\Vendor\VendorResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Master\Role;
use App\Models\InvoiceApproval;
use App\Models\Accounting\Invoice;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
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
                            ->maxValue(function (Get $get) {
                                return $get('invoice_amount');
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
                            ->rules([function () {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA' && !in_array($value, ['approved by oa', 'rejected'])) {
                                        $fail('You can Approve as OA Only.');
                                    }
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager' && !in_array($value, ['approved by account manager', 'rejected'])) {
                                        $fail('You can Approve as Accounts Manager Only.');
                                    }
                                    if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD' && !in_array($value, ['approved by md', 'rejected'])) {
                                        $fail('You can Approve as MD Only.');
                                    }
                                };
                            },])
                            ->required()
                            ->options([
                                'approved by oa' => 'Approved By Oa',
                                'approved by account manager' => 'Approved By Account Manager',
                                'approved by md' => 'Approved By MD',
                                'rejected' => 'Rejected',
                            ])
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    return in_array($record->status, ['approved by oa', 'rejected']);
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    return in_array($record->status, ['approved by account manager', 'rejected']);
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    return in_array($record->status, ['approved by md', 'rejected']);
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
                                if (in_array($record->status, ['rejected', 'approved by oa', 'approved by account manager', 'approved by md']) && Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    return true;
                                }
                                return false;
                            })
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
                        if(!InvoiceApproval::where('invoice_id',$record->id)->exists()){
                            if ($record->status == 'approved by oa') {
                                InvoiceApproval::create([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => 'approved by oa',
                                ]);
                            } else {
                                InvoiceApproval::create([
                                    'invoice_id' => $record->id,
                                    'status' => $record->status,
                                    'updated_by' => auth()->user()->id,
                                    'remarks' => $record->remarks,
                                ]);
                            }
                        }
                        Invoice::where('id', $record->id)
                            ->update([
                                'opening_balance' => $record->invoice_amount - $record->payment,
                                'balance' => $record->invoice_amount - $record->payment,
                                'payment' => $record->payment,
                                'status_updated_by' => auth()->user()->id,
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
