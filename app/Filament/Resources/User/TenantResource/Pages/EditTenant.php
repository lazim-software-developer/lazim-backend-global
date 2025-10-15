<?php

namespace App\Filament\Resources\User\TenantResource\Pages;

use App\Filament\Resources\User\TenantResource;
use Filament\Actions;
use App\Models\User\User;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use App\Models\Master\Role;
use App\Models\Building\FlatTenant;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;

class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            backButton(url: url()->previous())->visible(fn () => auth()->user()?->owner_association_id === 1), // TODO: Change this to the correct association ID or condition
            //Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave()
    {
        if (Role::where('id', auth()->user()->role_id)->first()->name == 'OA') {
            $user = User::where('owner_association_id', $this->record->owner_association_id)->first();
            $UserID=$user->id;
        }else{
            $UserID=auth()->user()?->id;
        }
        FlatTenant::updateOrCreate(
            ['tenant_id' => $UserID, 'flat_id' => $this->record->flat_id],
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
    }
}
