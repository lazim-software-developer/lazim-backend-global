<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\User\User;
use Filament\Tables\Table;
use App\Models\Master\Role;
use Filament\Resources\Resource;
use App\Models\Accounting\Invoice;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\InvoiceApproval;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        Section::make('Invoice Details')
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
                            ->label('Building name'),
                        Select::make('contract_id')
                            ->relationship('contract', 'contract_type')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Contract type'),
                        Select::make('vendor_id')
                            ->relationship('vendor', 'name')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Vendor name'),
                        TextInput::make('invoice_number')
                            ->required()
                            ->disabled()
                            ->maxLength(255),
                        Select::make('wda_id')
                            ->relationship('wda', 'job_description')
                            ->preload()
                            ->disabled()
                            ->searchable()
                            ->label('Job Description (WDA)'),
                        DatePicker::make('date')
                            ->rules(['date'])
                            ->required()
                            ->disabled()
                            ->label('Start Date'),
                    ]),
            ]),

        Section::make('Financial Information')
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        TextInput::make('opening_balance')
                            ->prefix('AED')
                            ->readOnly()
                            ->live(),
                        TextInput::make('payment')
                            ->prefix('AED')
                            ->numeric()
                            ->minValue(1)
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    return true;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
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
                                    return false;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    return false;
                                }
                            })
                            ->live(),
                        TextInput::make('balance')
                            ->prefix('AED')
                            ->readOnly()
                            ->live(),
                        TextInput::make('invoice_amount')
                            ->label('Invoice Amount')
                            ->disabled()
                            ->prefix('AED'),
                    ]),
            ]),
        Section::make('Documents')
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        FileUpload::make('document')
                            ->disk('s3')
                            ->directory('dev')
                            ->disabled()
                            ->openable(true)
                            ->downloadable(true)
                            ->label('Document'),
                    ]),
            ]),
        Section::make('Approval and Remarks')
            ->schema([
                Grid::make([
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 2,
                ])
                    ->schema([
                        Select::make('status')
                            ->required()
                            ->options([
                                'approved' => 'Approve',
                                'rejected' => 'Reject',
                            ])
                            ->disabled(function (Invoice $record) {
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)
                                        ->where('active', true)
                                        ->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)
                                            ->whereIn('role_id', Role::whereIn('name', ['OA', 'Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)
                                        ->where('active', true)
                                        ->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)
                                            ->whereIn('role_id', Role::whereIn('name', ['Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)
                                        ->where('active', true)
                                        ->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)
                                            ->whereIn('role_id', Role::whereIn('name', ['MD'])->pluck('id'))->pluck('id'))->exists();
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
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)
                                        ->where('active', true)
                                        ->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)
                                            ->whereIn('role_id', Role::whereIn('name', ['OA', 'Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)
                                        ->where('active', true)
                                        ->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)
                                            ->whereIn('role_id', Role::whereIn('name', ['Accounts Manager', 'MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                                if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
                                    $invoiceapproval = InvoiceApproval::where('invoice_id', $record->id)
                                        ->where('active', true)
                                        ->whereIn('updated_by', User::where('owner_association_id', auth()->user()?->owner_association_id)
                                            ->whereIn('role_id', Role::whereIn('name', ['MD'])->pluck('id'))->pluck('id'))->exists();
                                    return $invoiceapproval;
                                }
                            })
                            ->live()
                            ->required(),
                    ]),
            ]),

        
    ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('building.name')
                    ->label('Building'),
                TextColumn::make('contract.contract_type')
                    ->label('Contract type'),
                TextColumn::make('invoice_number')
                    ->label('Invoice Number'),
                TextColumn::make('wda.job_description')
                    ->label('Job Description(WDA)'),
                TextColumn::make('date')
                    ->default('NA')
                    ->label('Start date'),
                TextColumn::make('status')
                    ->default('NA')
                    ->label('Status'),
                TextColumn::make('user.first_name')
                    ->default('NA')
                    ->label('Status updated by'),
                TextColumn::make('invoice_amount')
                    ->default('NA')
                    ->label('Invoice amount'),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'approved' => 'Approve',
                        'rejected' => 'Reject',
                    ])
                    ->searchable(),
                SelectFilter::make('building_id')
                    ->relationship('building', 'name', function (Builder $query) {
                        if (Role::where('id', auth()->user()->role_id)->first()->name != 'Admin') {
                            $query->where('owner_association_id', Filament::getTenant()?->id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->label('Building'),
                SelectFilter::make('Invoice Status')
                ->options([
                    'paid'=>'Paid',
                    'unpaid'=>'Unpaid',
                    'partially_paid'=>'Partially Paid'
                ])
                ->query(function (Builder $query, $data) {
                    if ($data['value'] === 'paid') {
                        $query->whereColumn('invoice_amount', '=', 'payment');
                    } elseif ($data['value'] === 'unpaid') {
                        $query->where('payment', '=', 0);
                    } elseif ($data['value'] === 'partially_paid') {
                        $query->whereColumn('invoice_amount', '>', 'payment')
                              ->where('payment', '>', 0);
                    }
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                ExportBulkAction::make(),
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_any_invoice');
    }

    public static function canView(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('view_invoice');
    }

    public static function canCreate(): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('create_invoice');
    }

    public static function canEdit(Model $record): bool
    {
        $user = User::find(auth()->user()->id);
        return $user->can('update_invoice');
    }
}
