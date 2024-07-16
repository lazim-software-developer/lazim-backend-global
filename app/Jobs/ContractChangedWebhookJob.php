<?php

namespace App\Jobs;

use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\MollakTenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContractChangedWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $propertyGroupId,protected $contractNumber)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $propertyGroupId =$this->propertyGroupId;
        $contractNumber =$this->contractNumber;
        try{
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/property/".$propertyGroupId."/contract/".$contractNumber);
        $responce = $results->json()['response'];
        Log::info($responce);
        $flat_id = Flat::where('mollak_property_id',$responce['property']['propertyId'])->first();
        MollakTenant::updateOrCreate([
            'contract_number' => $responce['contract']['contractNumber']
        ],
        [
            'name' => $responce['tenant']['person']? $responce['tenant']['person']['name']['englishName'] : $responce['tenant']['company']['name']['englishName'],
            'email' => $responce['tenant']['person']? $responce['tenant']['person']['email'] : $responce['tenant']['company']['email'],
            'mobile' => $responce['tenant']['person']? $responce['tenant']['person']['mobile'] : $responce['tenant']['company']['mobile'],
            'contract_status' => $responce['contract']['status'],
            'start_date' => $responce['contract']['startDate'],
            'end_date' => $responce['contract']['endDate'],
            'flat_id' => $flat_id?->id,
            'building_id' => $flat_id?->building_id,
            'owner_association_id' => $flat_id?->owner_association_id,
        ]);

        if($responce['contract']['status'] == 'Terminated'){
            $flat = FlatTenant::where('flat_id',$flat_id?->id)->first();
            $flat->update([
                'active' => false,
                'end_date' => $responce['contract']['endDate'],
            ]);
        }
        } catch (\Exception $e) {
            Log::error('Failed to fetch contract changed ');
        }
    }
}
