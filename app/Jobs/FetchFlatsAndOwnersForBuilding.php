<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Building\Flat;
use App\Jobs\FetchOwnersForFlat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FetchFlatsAndOwnersForBuilding implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $building;
    protected $source;

    public function __construct($building,$source)
    {
        $this->building = $building;
        $this->source = $source;
    }

    public function handle()
    {
        try {
            $response = Http::withOptions(['verify' => false])->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get(env("MOLLAK_API_URL") . "/sync/propertygroups/" . $this->building->property_group_id . "/units");

            $data = $response->json();

            if ($data['response'] != null) {
                foreach ($data['response']['units'] as $property) {
                    $flat = Flat::firstOrCreate(
                        [
                            'property_number' => $property['unitNumber'],
                            'mollak_property_id' => $property['mollakPropertyId'],
                            'building_id' => $this->building->id,
                            'owner_association_id' => $this->building->owner_association_id,
                        ],
                        [
                            'plot_number' => $property['plotNumber'],
                            'suit_area' => $property['suitArea'],
                            'actual_area' => $property['actualArea'],
                            'balcony_area' => $property['balconyArea'],
                            'applicable_area' => $property['applicableArea'],
                            'virtual_account_number' => $property['virtualAccountNumber'],
                            'parking_count' => $property['parkingCount'],
                            'property_type' => 'NA'
                        ]
                    );

                    // $connection = DB::connection('lazim_accounts');
                    // $connection->table('flats')->insert([
                    //         'property_number' => $property['unitNumber'],
                    //         'mollak_property_id' => $property['mollakPropertyId'],
                    //         'building_id' => $this->building->id,
                    //         'owner_association_id' => $this->building->owner_association_id,
                    //         'plot_number' => $property['plotNumber'],
                    //         'suit_area' => $property['suitArea'],
                    //         'actual_area' => $property['actualArea'],
                    //         'balcony_area' => $property['balconyArea'],
                    //         'applicable_area' => $property['applicableArea'],
                    //         'virtual_account_number' => $property['virtualAccountNumber'],
                    //         'parking_count' => $property['parkingCount'],
                    //         'property_type' => 'NA'
                    // ]);

                    // Dispatch job to fetch owners for the flat
                    if($this->source == 'Mollak'){
                        FetchOwnersForFlat::dispatch($flat);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("no responce from mollak". $this->building->property_group_id);
        }
    }
}
