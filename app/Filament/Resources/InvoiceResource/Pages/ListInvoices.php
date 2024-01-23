<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource;
use App\Models\InvoiceApproval;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;
    protected static ?string $title = 'Invoice';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
    protected function getTableQuery(): Builder
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
            return parent::getTableQuery()->whereIn('vendor_id', Vendor::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'));
        } elseif (Role::where('id', auth()->user()->role_id)->first()->name == 'Accounts Manager') {
            $invoiceapproval = InvoiceApproval::whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['Accounts Manager','MD'])->pluck('id'))->pluck('id'))->whereIn('status',['approved','rejected'])->pluck('invoice_id');
            $invoiceapprovaloa = InvoiceApproval::whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['OA'])->pluck('id'))->pluck('id'))->where('status','approved')->pluck('invoice_id');
            return parent::getTableQuery()->whereIn('vendor_id', Vendor::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'))->whereIn('id',$invoiceapproval->merge($invoiceapprovaloa));
        } elseif (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
            $invoiceapproval = InvoiceApproval::whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['MD'])->pluck('id'))->pluck('id'))->whereIn('status',['approved','rejected'])->pluck('invoice_id');
            $invoiceapprovaloa = InvoiceApproval::whereIn('updated_by',User::where('owner_association_id',auth()->user()->owner_association_id)->whereIn('role_id',Role::whereIn('name',['Accounts Manager'])->pluck('id'))->pluck('id'))->where('status','approved')->pluck('invoice_id');
            return parent::getTableQuery()->whereIn('vendor_id', Vendor::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'))->whereIn('id',$invoiceapproval->merge($invoiceapprovaloa));
        }
        return parent::getTableQuery();
    }
}
