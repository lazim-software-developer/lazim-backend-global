<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\Master\Role;
use App\Models\InvoiceApproval;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\InvoiceResource;
use App\Jobs\InvoiceRejectionJob;
use App\Models\Accounting\Invoice;
use App\Models\User\User;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;
    protected static ?string $title = 'Invoice';

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
        return $data;
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status_updated_by'] = auth()->user()->id;
 
        return $data;
    }
    protected function afterSave(): void
    {
        $invoiceApproval = InvoiceApproval::where('invoice_id',$this->record->id)->first();
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'OA' && !InvoiceApproval::where('invoice_id',$this->record->id)->exists()){
            if($this->record->status == 'approved by oa'){
                InvoiceApproval::create([
                    'invoice_id' => $this->record->id,
                    'status' => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks' => 'approved by oa',
                ]);
            }else{
                InvoiceApproval::create([
                    'invoice_id' => $this->record->id,
                    'status' => $this->record->status,
                    'updated_by' => auth()->user()->id,
                    'remarks' => $this->record->remarks,
                ]);
            }
        }
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager'){
            $invoiceApproval->status = $this->record->status;
            $invoiceApproval->updated_by = auth()->user()->id;
            if($this->record->status == 'approved by account manager'){
                $invoiceApproval->remarks = 'approved by account manager';
            }
            else{
                $invoiceApproval->remarks = $this->record->remarks;
                $notify = User::where(['owner_association_id'=>auth()->user()->owner_association_id,'role_id'=>Role::where('name','OA')->first()->id])->first();
                Notification::make()
                        ->success()
                        ->title("Invoice Rejection")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('We regret to inform that invoice '.$this->record->invoice_number.' has been rejected by Account Manager '.auth()->user()->first_name.'.')
                        ->sendToDatabase($notify);
                $user = User::find($this->record->created_by);
                $invoice = Invoice::find($this->record->id);
                InvoiceRejectionJob::dispatch($user, $this->record->remarks, $invoice);
            }
            $invoiceApproval->save();
        }
        if(Role::where('id', auth()->user()->role_id)->first()->name == 'MD'){
            $invoiceApproval->status = $this->record->status;
            $invoiceApproval->updated_by = auth()->user()->id;
            if($this->record->status == 'approved by md'){
                $invoiceApproval->remarks = 'approved by md';
            }
            else{
                $invoiceApproval->remarks = $this->record->remarks;
                $notifyoa = User::where(['owner_association_id'=>auth()->user()->owner_association_id,'role_id'=>Role::where('name','OA')->first()->id])->first();
                $notifyacc = User::where(['owner_association_id'=>auth()->user()->owner_association_id,'role_id'=>Role::where('name','Accounts Manager')->first()->id])->first();
                Notification::make()
                        ->success()
                        ->title("Invoice Rejection")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('We regret to inform that invoice '.$this->record->invoice_number.' has been rejected by MD '.auth()->user()->first_name.'.')
                        ->sendToDatabase($notifyoa);
                Notification::make()
                        ->success()
                        ->title("Invoice Rejection")
                        ->icon('heroicon-o-document-text')
                        ->iconColor('warning')
                        ->body('We regret to inform that invoice '.$this->record->invoice_number.' has been rejected by MD '.auth()->user()->first_name.'.')
                        ->sendToDatabase($notifyacc);
                $user = User::find($this->record->created_by);
                $invoice = Invoice::find($this->record->id);
                InvoiceRejectionJob::dispatch($user, $this->record->remarks, $invoice);
            }
            $invoiceApproval->save();
        }
        Invoice::where('id', $this->data['id'])
            ->update([
                'opening_balance' => $this->data['invoice_amount'] - $this->data['payment'],
                'balance' => $this->data['invoice_amount'] - $this->data['payment'],
            ]);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
