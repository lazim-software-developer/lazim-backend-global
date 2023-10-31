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
use Illuminate\Support\Facades\Log;

class FetchFlatsAndOwnersForBuilding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $building;

    public function __construct($building)
    {
        $this->building = $building;
    }

    public function handle()
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID", "dqHdShhrZQgeSY9a4BZh6cgucpQJvS5r"),
        ])->get(env("MOLLAK_API_URL", "https://b2bgateway.dubailand.gov.ae/mollak/external") . "/sync/owners/" . $this->building->property_group_id);

        Log::info("FetchFlatsAndOwnersForBuilding", [3]);

        $data = $response->json();
        if ($data['response'] != null) {
            foreach ($data['response']['properties'] as $property) {
                $flat = Flat::create([
                    'property_number' => $property['propertyNumber'],
                    'mollak_property_id' => $property['mollakPropertyId'],
                    'property_type' => $property['propertyType'],
                    'building_id' => $this->building->id,
                    'owner_association_id' => $this->building->owner_association_id,
                ]);

                foreach ($property['owners'] as $ownerData) {
                    $owner = ApartmentOwner::firstOrCreate([
                        'owner_number' => $ownerData['ownerNumber'],
                    ], [
                        'email' => $ownerData['email'],
                        'name' => $ownerData['name']['englishName'],
                        'mobile' => $ownerData['mobile'],
                        'passport' => $ownerData['passport'],
                        'emirates_id' => $ownerData['emiratesId'],
                        'trade_license' => $ownerData['tradeLicence'],
                    ]);

                    // Attach the owner to the flat
                    $flat->owners()->attach($owner->id);
                }
            }
        }
    }
}
