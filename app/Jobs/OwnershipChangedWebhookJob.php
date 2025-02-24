<?php

namespace App\Jobs;

use App\Models\ApartmentOwner;
use App\Models\Building\Flat;
use App\Models\FlatOwners;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OwnershipChangedWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $propertyGroupId, protected $mollakPropertyId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $propertyGroupId = $this->propertyGroupId;
        $mollakPropertyId = $this->mollakPropertyId;
        try{
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/owners/".$propertyGroupId."/".$mollakPropertyId);
        $responce = $results->json()['response'];
        Log::info($results->json());
        foreach($responce['properties'] as $property){
            $flat = Flat::where('mollak_property_id',$property['mollakPropertyId'])->first();
            $flatOwner = FlatOwners::where('flat_id',$flat?->id)->update(['active'=> false]);
            foreach($property['owners'] as $units){

                $owner = ApartmentOwner::firstOrCreate([
                    'owner_number' => $units['ownerNumber'],
                    'email' => $units['email'],
                    'mobile' => $units['email'],
                ],[
                    'name' => $units['name']['englishName'],
                    'passport' => $units['email'],
                    'emirates_id' => $units['emiratesId'],
                    'trade_license' => $units['tradeLicence']
                ]);
                FlatOwners::insert([
                    'owner_id' => $owner?->id,
                    'flat_id'=> $flat?->id,
                    'active' => true
                ]);
            }
        }
    } catch (\Exception $e) {
        Log::error('Failed to fetch ownership changed ');
    }
    }

}
