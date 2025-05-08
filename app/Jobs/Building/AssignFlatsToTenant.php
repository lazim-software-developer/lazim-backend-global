<?php

namespace App\Jobs\Building;

use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignFlatsToTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function __construct(protected $email, protected $mobile, protected $owner_id, protected $customerId,protected $type)
    {

    }

    public function handle()
    {
        // Fetch the owner using the provided email
        $owner = ApartmentOwner::where('id', $this->owner_id)->first();

        $user = User::where('email', $this->email)->where('phone',$this->mobile)->where('owner_id', $this->owner_id)->first();

        if (!$owner) {
            // No owner found with the given email
            return;
        }

        if (!$user) {
            // No user found with the given email, mobile and owner_id
            return;
        }

        // Fetch all flats that match the owner's email
        $flats = DB::table('flat_owner')->where('owner_id', $owner->id)
            ->join('flats', 'flats.id', 'flat_owner.flat_id')
            ->selectRaw('MAX(flats.id) as flat_id')
            ->groupBy('flats.building_id', 'flats.property_number')
            ->get();

        $connection = DB::connection('lazim_accounts');
        foreach ($flats as $flat) {
            // Add an entry in the flat_tenant table for each flat
            $flatDetails = Flat::find($flat->flat_id);
            FlatTenant::updateOrCreate(
                ['tenant_id' => $user->id, 'flat_id' => $flatDetails->id],
                [
                    'tenant_id' => $user->id,
                    'flat_id' => $flatDetails->id,
                    'building_id' => $flatDetails->building_id,
                    'owner_association_id' => $flatDetails->owner_association_id,
                    'start_date' => now(),
                    'active' => 1,
                    'role' => $this->type,
                ]
            );

            // $connection->table('customer_flat')->insert([
            //     'customer_id' => $this->customerId,
            //     'flat_id' => $flat->flat_id,
            //     'building_id' => $flatDetails->building_id,
            //     'property_number' =>$flatDetails->property_number
            // ]);
            $created_by = $connection->table('users')->where(['type' => 'building', 'building_id' => $flatDetails->building_id])->first()->id;
            $building = Building::find($flatDetails->building_id);
            $name = $user->first_name . ' - ' . $flatDetails->property_number;
            $primary = $connection->table('customers')->where('flat_id', $flatDetails->id)->where('type', 'Owner')->where('primary',true)->exists();
            $connection->table('customers')->updateOrInsert(
                [
                    'created_by' => $created_by,
                    'building_id' => $flatDetails->building_id,
                    'email' => $this->email,
                    'contact' => $this->mobile,
                ],[
                'customer_id' => $this->customerId+1,
                'name' => $name,
                'email' => $this->email,
                'contact' => $this->mobile,
                'type' => $this->type,
                'lang' => 'en',
                'created_by' => $created_by,
                'is_enable_login' => 0,
                'billing_name' => $name,
                'billing_country' => 'UAE',
                'billing_city' => 'Dubai',
                'billing_phone' => $this->mobile,
                'billing_address' => $building->address_line1 . ', ' . $building->area,
                'shipping_name' => $name,
                'shipping_country' => 'UAE',
                'shipping_city' => 'Dubai',
                'shipping_phone' => $this->mobile,
                'shipping_address' => $building->address_line1 . ', ' . $building->area,
                'created_by_lazim' => true,
                'flat_id' => $flatDetails->id,
                'building_id' => $flatDetails->building_id,
                'primary' => !$primary,
            ]);
        }
    }
}
