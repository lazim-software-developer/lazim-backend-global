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
                    ], [
                        'name' => $ownerData['name']['englishName'],
                        'passport' => $ownerData['passport'],
                        'emirates_id' => $ownerData['emiratesId'],
                        'trade_license' => $ownerData['tradeLicence'],
                    ]);

                    // Attach the owner to the flat
                    $this->flat->owners()->syncWithoutDetaching($owner->id);
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
