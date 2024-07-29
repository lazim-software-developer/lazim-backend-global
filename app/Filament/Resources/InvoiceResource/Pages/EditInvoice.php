<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Jobs\InvoiceRejectionJob;
use App\Models\AccountCredentials;
use App\Models\Accounting\Invoice;
use App\Models\InvoiceApproval;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;
    protected static ?string $title   = 'Invoice';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($data['status'] == 'pending') {
            $data['status'] = null;
        }
        $data['remarks'] = null;
        return $data;
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status_updated_by'] = auth()->user()->id;
        return $data;
    }
    protected function afterSave(): void
    {
        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
        // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');
        $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host' => $credentials->host ?? env('MAIL_HOST'),
            'mail_port' => $credentials->port ?? env('MAIL_PORT'),
            'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA' && !InvoiceApproval::where('invoice_id', $this->record->id)->where('active', true)->exists()) {

            if ($this->record->status == 'approved') {
                InvoiceApproval::firstOrCreate([
                    'invoice_id' => $this->record->id,
                    'status'     => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks'    => 'approved by oa',
                    'active'     => true,
                ]);
            } else {
                InvoiceApproval::firstOrCreate([
                    'invoice_id' => $this->record->id,
                    'status'     => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks'    => $this->record->remarks,
                    'active'     => true,
                ]);
                $user    = User::find($this->record->created_by);
                $invoice = Invoice::find($this->record->id);
                InvoiceRejectionJob::dispatch($user, $this->record->remarks, $invoice, $mailCredentials);
            }
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
            if ($this->record->status == 'approved') {
                InvoiceApproval::firstOrCreate([
                    'invoice_id' => $this->record->id,
                    'status'     => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks'    => 'approved by Account Manager',
                    'active'     => true,
                ]);
            } else {
                InvoiceApproval::firstOrCreate([
                    'invoice_id' => $this->record->id,
                    'status'     => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks'    => $this->record->remarks,
                    'active'     => true,
                ]);
                $notify = User::where(['owner_association_id' => auth()->user()?->owner_association_id, 'role_id' => Role::where('name', 'OA')->first()->id])->first();
                Notification::make()
                    ->success()
                    ->title("Invoice Rejection")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->body('We regret to inform that invoice ' . $this->record->invoice_number . ' has been rejected by Account Manager ' . auth()->user()->first_name . '.')
                    ->sendToDatabase($notify);
                $user    = User::find($this->record->created_by);
                $invoice = Invoice::find($this->record->id);
                InvoiceRejectionJob::dispatch($user, $this->record->remarks, $invoice, $mailCredentials);
            }
        }
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
            if ($this->record->status == 'approved') {
                InvoiceApproval::firstOrCreate([
                    'invoice_id' => $this->record->id,
                    'status'     => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks'    => 'approved by md',
                    'active'     => true,
                ]);
            } else {
                InvoiceApproval::firstOrCreate([
                    'invoice_id' => $this->record->id,
                    'status'     => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks'    => $this->record->remarks,
                    'active'     => true,
                ]);
                $notifyoa  = User::where(['owner_association_id' => auth()->user()?->owner_association_id, 'role_id' => Role::where('name', 'OA')->first()->id])->first();
                $notifyacc = User::where(['owner_association_id' => auth()->user()?->owner_association_id, 'role_id' => Role::where('name', 'Accounts Manager')->first()->id])->get();
                // dd($notifyacc);
                Notification::make()
                    ->success()
                    ->title("Invoice Rejection")
                    ->icon('heroicon-o-document-text')
                    ->iconColor('warning')
                    ->body('We regret to inform that invoice ' . $this->record->invoice_number . ' has been rejected by MD ' . auth()->user()->first_name . '.')
                    ->sendToDatabase($notifyoa);
                foreach ($notifyacc as $user) {
                    Notification::make()
                        ->success()
                        ->title("Invoice Rejection")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('We regret to inform that invoice ' . $this->record->invoice_number . ' has been rejected by MD ' . auth()->user()->first_name . '.')
                        ->sendToDatabase($user);
                }

                $user    = User::find($this->record->created_by);
                $invoice = Invoice::find($this->record->id);
                InvoiceRejectionJob::dispatch($user, $this->record->remarks, $invoice, $mailCredentials);
            }
        }
        Invoice::where('id', $this->data['id'])
            ->update([
                'opening_balance' => $this->data['invoice_amount'] - $this->data['payment'],
                'balance'         => $this->data['invoice_amount'] - $this->data['payment'],
            ]);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
