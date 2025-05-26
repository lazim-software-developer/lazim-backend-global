<?php

namespace App\Jobs;

use App\Jobs\Building\AssignFlatsToTenant;
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
use Illuminate\Support\Facades\Log;

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
                    // Delete record based on owner number
                    ApartmentOwner::where('owner_number', $ownerData['ownerNumber'])->delete();
                    $phone = $this->cleanPhoneNumber($ownerData['mobile']);
                        $owner = ApartmentOwner::withTrashed()->updateOrCreate([
                            'owner_number' => $ownerData['ownerNumber'],
                            'email' => $ownerData['email'],
                            'mobile' => $phone,
                            'owner_association_id' => $this->flat->owner_association_id,
                        ], [
                            'name' => $ownerData['name']['englishName'],
                            'passport' => $ownerData['passport'],
                            'emirates_id' => $ownerData['emiratesId'],
                            'trade_license' => $ownerData['tradeLicence'],
                            'primary_owner_mobile' => $phone,
                            'primary_owner_email' => $ownerData['email'],
                            'deleted_at' => null,
                        ]);

                    // Insert into mollak_unit_owner_histories
                    $apartmentOwner = ApartmentOwner::withTrashed()->where('owner_number', $ownerData['ownerNumber'])->get();
                    if($apartmentOwner->count() > 0){
                        foreach ($apartmentOwner as $owner) {
                            DB::table('mollak_unit_owner_histories')->insert([
                                'flat_id' => $this->flat->id,
                                'owner_number' => $owner->owner_number,
                                'email' => $owner->email,
                                'mobile' => $owner->mobile,
                                'owner_association_id' => $this->flat->owner_association_id,
                                'status' => $owner->deleted_at ? 'Detached' : 'Attached',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                    $building = Building::find($this->flat->building_id);
                    $connection = DB::connection('lazim_accounts');
                    // $created_by = $connection->table('users')->where('owner_association_id', $this->flat->owner_association_id)->where('type', 'company')->first()?->id;
                    $buildingUser = $connection->table('users')->where(['type' => 'building', 'building_id' => $building->id])->first();
                    $customer = $connection->table('customers')->where('created_by', $buildingUser?->id)->orderByDesc('customer_id')->first();
                    $customerId = $customer ? $customer->customer_id + 1 : 1;
                    $name = $ownerData['name']['englishName'] . ' - ' . $this->flat->property_number;

                    $connection->table('customers')->updateOrInsert(
                        [
                            'created_by' => $buildingUser?->id,
                            'building_id' => $this->flat->building_id,
                            'email' => $ownerData['email'],
                            'contact' => $phone,
                        ],
                        [
                            'customer_id' => $customerId,
                            'name' => $name,
                            'email' => $ownerData['email'],
                            'contact' => $phone,
                            'type' => 'Owner',
                            'lang' => 'en',
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
                            'updated_at' => now(), // Ensure the updated_at timestamp is updated
                            'created_at' => now(), // Only relevant for insert
                        ]
                    );

                    // Log::info('owner',[$owner]);
                    // Attach the owner to the flat
                    $ownerId=$owner->id;
                    if (!empty($owner)) {
                        $this->flat->owners()->sync($ownerId);
                        // Find all the flats that this user is owner of and attach them to flat_tenant table using the job
                        AssignFlatsToTenant::dispatch($ownerData['email'], $phone, $ownerId, $customerId, 'Owner')->delay(now()->addSeconds(5));
                    }
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
