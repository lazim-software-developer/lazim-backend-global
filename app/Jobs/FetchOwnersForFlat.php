<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Building\Flat;
use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use Illuminate\Support\Facades\DB;

class FetchOwnersForFlat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $flat;

    public function __construct(Flat $flat)
    {
        $this->flat = $flat;
    }

    public function handle()
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/owners/" . $this->flat->building->property_group_id . "/" . $this->flat->mollak_property_id);

        $ownerData = $response->json();

        if ($ownerData['response'] != null) {
            foreach ($ownerData['response']['properties'] as $property) {
                // Update property_type for a flat
                $this->flat->update(['property_type' => $property['propertyType']]);

                foreach ($property['owners'] as $ownerData) {
                    $phone = $this->cleanPhoneNumber($ownerData['mobile']);

                    $owner = ApartmentOwner::firstOrCreate([
                        'owner_number' => $ownerData['ownerNumber'],
                        'email' => $ownerData['email'],
                        'mobile' => $phone,
                        'owner_association_id' => $this->flat->owner_association_id,
                    ], [
                        'name' => $ownerData['name']['englishName'],
                        'passport' => $ownerData['passport'],
                        'emirates_id' => $ownerData['emiratesId'],
                        'trade_license' => $ownerData['tradeLicence'],
                    ]);

                    $building = Building::find($this->flat->building_id);
                    $connection = DB::connection('lazim_accounts');
                    $created_by = $connection->table('users')->where('owner_association_id', $this->flat->owner_association_id)->where('type', 'company')->first()?->id;
                    $customer = $connection->table('customers')->where('created_by', $created_by)->orderByDesc('customer_id')->first();
                    $customerId = $customer ? $customer->customer_id + 1 : 1;
                    $name = $ownerData['name']['englishName'] . ' - ' . $this->flat->property_number;
                    $connection->table('customers')->insert([
                        'customer_id' => $customerId,
                        'name' => $name,
                        'email'                => $ownerData['email'],
                        'contact' => $phone,
                        'type' => 'Owner',
                        'lang' => 'en',
                        'created_by' => $created_by,
                        'is_enable_login' => 0,
                        'billing_name' => $name,
                        'billing_country' => 'UAE',
                        'billing_city' => 'Dubai',
                        'billing_phone' => $phone,
                        'billing_address' => $building->address_line1 . ', ' . $building->area,
                        'shipping_name' => $name,
                        'shipping_country' => 'UAE',
                        'shipping_city' => 'Dubai',
                        'shipping_phone' => $phone,
                        'shipping_address' => $building->address_line1 . ', ' . $building->area,
                        'created_by_lazim' => true,
                        'flat_id' => $this->flat->id,
                        'building_id' => $this->flat->building_id,
                    ]);

                    // Attach the owner to the flat
                    $this->flat->owners()->syncWithoutDetaching($owner->id);

                    // $customer = $connection->table('customers')->where([
                    //     'email' => $ownerData['email'],
                    //     'contact' => $phone
                    // ])->first();
                    // $connection->table('customer_flat')->insert([
                    //     'customer_id' => $customer?->id,
                    //     'flat_id' => $this->flat->id,
                    //     'building_id' => $this->flat->building_id,
                    //     'property_number' => $this->flat->property_number
                    // ]);
                }
            }
        }
    }

    function cleanPhoneNumber($phoneNumber)
    {
        // Remove -, +, and | characters
        $cleaned = preg_replace('/[-+|]/', '', $phoneNumber);

        // Remove leading zeros
        $cleaned = ltrim($cleaned, '0');

        return $cleaned;
    }
}
