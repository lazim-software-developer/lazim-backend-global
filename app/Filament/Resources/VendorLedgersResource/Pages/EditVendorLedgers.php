<?php

namespace App\Filament\Resources\VendorLedgersResource\Pages;

use App\Filament\Resources\VendorLedgersResource;
use App\Models\Accounting\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorLedgers extends EditRecord
{
    protected static string $resource = VendorLedgersResource::class;
    protected static ?string $title = 'Service provider ledgers';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public function afterSave()
    {

        Invoice::where('id', $this->data['id'])
            ->update([
                'opening_balance' => $this->data['invoice_amount'] - $this->data['payment'],
                'balance' => $this->data['invoice_amount'] - $this->data['payment'],
            ]);

    }
}
