<?php

namespace App\Filament\Resources\User\OwnerResource\Pages;

use App\Filament\Resources\User\OwnerResource;
use Filament\Actions;
use App\Models\FlatOwners;
use App\Models\Building\Flat;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Builder;

class EditOwner extends EditRecord
{
    protected static string $resource = OwnerResource::class;
    
    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }

    public function afterSave()
    {
                    $building = Building::find($this->record->building_id);
                    $Assignnflats = FlatOwners::where('owner_id',$this->record->id)->get();
                    $connection = DB::connection(env('SECOND_DB_CONNECTION'));
                    foreach($Assignnflats as $flat_value){
                    $flatDetail=Flat::where('id',$flat_value->flat_id)->first();
                    $customer = $connection->table('customers')->where('created_by', auth()->user()?->id)->orderByDesc('customer_id')->first();
                    $customerId = $customer ? $customer->customer_id + 1 : 1;
                    $name = $this->record->name . ' - ' . $flatDetail->property_number;

                    $connection->table('customers')->updateOrInsert(
                        [
                            'created_by' => auth()->user()?->id,
                            'building_id' => $this->record->building_id,
                            'flat_id' =>$flat_value->flat_id,
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
    }
}
