<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use Filament\Actions;
use App\Models\FlatOwners;
use App\Models\Building\Flat;
use Filament\Facades\Filament;
use App\Models\Building\Building;
use App\Models\AccountCredentials;
use Illuminate\Support\Facades\DB;
use App\Jobs\WelcomeNotificationJob;
use App\Jobs\WelcomeOwnerNotificationJob;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\User\OwnerResource;

class CreateOwner extends CreateRecord
{
    protected static string $resource = OwnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn() => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
        ];
    }
    public function afterCreate()
    {
        $building = Building::find($this->record->building_id);
        $Assignnflats = FlatOwners::where('owner_id', $this->record->id)->get();
        $connection = DB::connection(env('SECOND_DB_CONNECTION'));
        foreach ($Assignnflats as $flat_value) {
            $flatDetail = Flat::where('id', $flat_value->flat_id)->first();
            $customer = $connection->table('customers')->where('created_by', auth()->user()?->id)->orderByDesc('customer_id')->first();
            $customerId = $customer ? $customer->customer_id + 1 : 1;
            $name = $this->record->name . ' - ' . $flatDetail->property_number;

            $connection->table('customers')->updateOrInsert(
                [
                    'created_by' => auth()->user()?->id,
                    'building_id' => $this->record->building_id,
                    'flat_id' => $flat_value->flat_id,
                    'email' => $this->record->email,
                    'contact' => $this->record->mobile,
                ],
                [
                    'customer_id' => $customerId,
                    'name' => $this->record->name,
                    'email' => $this->record->email,
                    'contact' => $this->record->mobile,
                    'type' => 'Owner',
                    'lang' => 'en',
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
                    'flat_id' => $flat_value->flat_id,
                    'building_id' => $building->id,
                    'updated_at' => now(), // Ensure the updated_at timestamp is updated
                    'created_at' => now(), // Only relevant for insert
                ]
            );
            // Attach the owner to the flat
            $flatDetail->owners()->syncWithoutDetaching($this->record->id);
        }

        //Triger Email to Owner
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
