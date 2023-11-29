<?php

namespace App\Filament\Pages;

use App\Filament\Resources\OAMReceiptsResource;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ListAllReceipts extends Page implements HasTable
{
    use InteractsWithTable;
    public $invoice;

    protected static string $resource = OAMReceiptsResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.list-all-receipts';

    protected static ?string $slug = '{invoice}/receipts';

    public function mount(OAMInvoice $invoice) // Type-hint the Building model
    {
        $this->invoice = $invoice;
    }

    public function table(Table $table): Table
    {
        // dd($this->invoice->invoice_period);
        return $table
            ->query(OAMReceipts::query()->where('flat_id', $this->invoice->flat_id)->where('receipt_period', $this->invoice->invoice_period))
            ->columns([
                TextColumn::make('building.name'),
                TextColumn::make('flat.property_number'),
                TextColumn::make('receipt_number'),
                TextColumn::make('receipt_period'),
                TextColumn::make('receipt_date'),
                TextColumn::make('payment_mode'),
                TextColumn::make('receipt_amount'),
                TextColumn::make('payment_status'),
                TextColumn::make('transaction_reference'),
                TextColumn::make('virtual_account_description'),
            ]);
    }
}
