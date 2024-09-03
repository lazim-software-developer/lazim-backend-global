<?php

namespace App\Filament\Resources\Vendor\VendorResource\Pages;

use App\Filament\Resources\Vendor\VendorResource;
use App\Jobs\VendorAccountCreationJob;
use App\Jobs\VendorRejectionJob;
use App\Models\AccountCredentials;
use App\Models\User\User;
use App\Models\VendorRemarks;
use App\Models\Vendor\Vendor;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EditVendor extends EditRecord
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
    public function afterSave()
    {
        $userId         = User::find($this->record->owner_id);
        $userId->active = $this->record->active;
        $userId->save();
        if ($this->record->active) {
            VendorRemarks::firstorcreate([
                'vendor_id' => $this->record->id,
                'status'    => 'active',
                'remarks'   => $this->record->remarks ?? 'Approved',
                'user_id'   => auth()->user()->id,
            ]);
        } else {
            VendorRemarks::firstorcreate([
                'vendor_id' => $this->record->id,
                'status'    => 'inactive',
                'remarks'   => $this->record->remarks ?? 'Approved',
                'user_id'   => auth()->user()->id,
            ]);
        }
    }
    protected function beforeSave(): void
    {
        if ($this->record->status == null) {
            $oa_id  = Vendor::where('id', $this->data['id'])->first();
            $tenant = Filament::getTenant()?->id ?? $oa_id?->owner_association_id;
            // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');

            $credentials     = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host'         => $credentials->host ?? env('MAIL_HOST'),
                'mail_port'         => $credentials->port ?? env('MAIL_PORT'),
                'mail_username'     => $credentials->username ?? env('MAIL_USERNAME'),
                'mail_password'     => $credentials->password ?? env('MAIL_PASSWORD'),
                'mail_encryption'   => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
            ];

            if ($this->data['status'] == 'rejected') {
                $vendor         = Vendor::where('id', $this->data['id'])->first();
                $user           = User::find($vendor->owner_id);
                $password       = Str::random(12);
                $user->password = Hash::make($password);
                $user->save();
                $remarks = $this->data['remarks'];
                VendorRejectionJob::dispatch($user, $remarks, $password, $mailCredentials);
            }
            if ($this->data['status'] == 'approved') {
                $vendor         = Vendor::where('id', $this->data['id'])->first();
                $user           = User::find($vendor->owner_id);
                $password       = Str::random(12);
                $user->password = Hash::make($password);
                $user->save();
                VendorAccountCreationJob::dispatch($user, $password, $mailCredentials);

            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
