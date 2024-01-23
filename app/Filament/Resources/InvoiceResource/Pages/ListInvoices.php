<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\InvoiceResource;

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
            // Get the 'OA' role ID
            $oaRoleId = Role::where('name', 'OA')->first()->id;

            return parent::getTableQuery()
                ->whereIn('vendor_id', Vendor::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'))
                ->where(function ($query) use ($oaRoleId) {
                    $query->where(function ($q) use ($oaRoleId) {
                        // Include 'approved' for all and 'rejected' only if the status_updated_by user is not 'OA'
                        $q->where('status', 'approved')
                            ->orWhere(function ($q) use ($oaRoleId) {
                                $q->where('status', 'rejected')
                                    ->whereNotIn('status_updated_by', User::where('owner_association_id', auth()->user()->owner_association_id)
                                        ->whereIn('role_id', [$oaRoleId])
                                        ->pluck('id'));
                            });
                    });
                });
                
        } elseif (Role::where('id', auth()->user()->role_id)->first()->name == 'MD') {
            // Get the 'Accounts Manager' and 'OA' role IDs
            $accountsManagerRoleId = Role::where('name', 'Accounts Manager')->first()->id;
            $oaRoleId = Role::where('name', 'OA')->first()->id;

            return parent::getTableQuery()
                ->whereIn('vendor_id', Vendor::where('owner_association_id', auth()->user()->owner_association_id)->pluck('id'))
                ->where(function ($query) use ($accountsManagerRoleId, $oaRoleId) {
                    $query->where(function ($q) use ($accountsManagerRoleId, $oaRoleId) {
                        // Include 'approved' for all
                        $q->where('status', 'approved');

                        // Include 'rejected' only if the status_updated_by user is not 'Accounts Manager' or 'OA'
                        $q->orWhere(function ($q) use ($accountsManagerRoleId, $oaRoleId) {
                            $q->where('status', 'rejected')
                                ->whereNotIn('status_updated_by', User::where('owner_association_id', auth()->user()->owner_association_id)
                                    ->whereIn('role_id', [$accountsManagerRoleId, $oaRoleId])
                                    ->pluck('id'));
                        });
                    });
                });
        }
        return parent::getTableQuery();
    }
}
