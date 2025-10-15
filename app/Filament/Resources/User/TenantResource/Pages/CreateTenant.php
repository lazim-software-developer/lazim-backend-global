<?php

namespace App\Filament\Resources\User\TenantResource\Pages;

use Filament\Actions;
use App\Models\User\User;
use App\Models\Master\Role;
use Filament\Facades\Filament;
use App\Models\Building\Building;
use App\Models\AccountCredentials;
use Illuminate\Support\Facades\DB;
use App\Models\Building\FlatTenant;
use App\Jobs\WelcomeOwnerNotificationJob;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\User\TenantResource;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function afterCreate()
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
            $user = User::where('owner_association_id', $this->record->owner_association_id)->first();
            $UserID=$user->id;
        }else{
            $UserID=auth()->user()?->id;
        }

        FlatTenant::updateOrCreate(
            ['tenant_id' =>$UserID, 'flat_id' => $this->record->flat_id],
            [
                'tenant_id' => $UserID,
                'flat_id' => $this->record->flat_id,
                'building_id' => $this->record->building_id,
                'owner_association_id' => $this->record->owner_association_id,
                'start_date' => now(),
                'active' => 1,
                'role' => 'Tenant',
            ]
        );

        $building = Building::find($this->record->building_id);
        $connection = DB::connection(env('SECOND_DB_CONNECTION'));
        $customer = $connection->table('customers')->where('created_by', auth()->user()?->id)->orderByDesc('customer_id')->first();
        $customerId = $customer ? $customer->customer_id + 1 : 1;
        $primary = $connection->table('customers')->where('flat_id', $this->record->flat_id)
        ->where('building_id', $this->record->building_id)->where('created_by', auth()->user()?->id)->where('email', $this->record->email)->where('type', 'Tenant')->exists();
        $connection->table('customers')->updateOrInsert(
            [
                'created_by' => auth()->user()?->id,
                'building_id' => $this->record->building_id,
                'flat_id' => $this->record->flat_id,
                'email' => $this->record->email,
                'contact' => $this->record->mobile,
            ],[
            'customer_id' => $customerId,
            'name' => $this->record->name,
            'email' => $this->record->email,
            'contact' => $this->record->mobile,
            'type' => 'Tenant',
            'lang' => 'en',
            'created_by' => auth()->user()?->id,
            'is_enable_login' => 0,
            'billing_name' => $this->record->name,
            'billing_country' => 'UAE',
            'billing_city' => 'Dubai',
            'billing_phone' => $this->record->mobile,
            'billing_address' => $building->address_line1 . ', ' . $building->area,
            'shipping_name' => $this->record->name,
            'shipping_country' => 'UAE',
            'shipping_city' => 'Dubai',
            'shipping_phone' => $this->record->mobile,
            'shipping_address' => $building->address_line1 . ', ' . $building->area,
            'created_by_lazim' => true,
            'flat_id' =>$this->record->flat_id,
            'building_id' => $this->record->building_id,
            'primary' => !$primary,
        ]);

        $tenant           = Filament::getTenant()?->id ?? auth()->user()?->owner_association_id;
        $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
        $mailCredentials = [
            'mail_host' => $credentials->host ?? env('MAIL_HOST'),
            'mail_port' => $credentials->port ?? env('MAIL_PORT'),
            'mail_username' => $credentials->username ?? env('MAIL_USERNAME'),
            'mail_password' => $credentials->password ?? env('MAIL_PASSWORD'),
            'mail_encryption' => $credentials->encryption ?? env('MAIL_ENCRYPTION'),
            'mail_from_address' => $credentials->email ?? env('MAIL_FROM_ADDRESS'),
        ];
        $OaName = Filament::getTenant()?->name ?? 'Admin';
        WelcomeOwnerNotificationJob::dispatch($this->record->email, $this->record->name, $building->name, $mailCredentials, $OaName);
    }
}
