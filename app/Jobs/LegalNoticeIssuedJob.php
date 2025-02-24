<?php

namespace App\Jobs;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\LegalNotice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LegalNoticeIssuedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $propertyGroupId,protected $mollakPropertyId,protected $legalNoticeId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $propertyGroupId =$this->propertyGroupId;
        $mollakPropertyId =$this->mollakPropertyId;
        $legalNoticeId=$this->legalNoticeId;
        // Log::info('data'. $prope`rtyGroupId);
        try{
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/legalnotice/".$propertyGroupId."/".$mollakPropertyId."/".$legalNoticeId);
        // Log::info($results->json());

        $responces = $results->json()['response']['propertyGroups'];
        foreach($responces as $responce){
            Log::info($responce);
            $building_id = Building::where('property_group_id',$responce['propertyGroup']['id'])->first();
            $oam_id = DB::table('building_owner_association')->where('building_id',$building_id?:null)->where('active', true)->first();
            foreach($responce['mollakProperties'] as $notice){

                $flat_id = Flat::where('mollak_property_id',$notice['mollakPropertyId'])->first();

                $legalNotice = LegalNotice::updateOrCreate([
                    'building_id' => $building_id?->id,
                    'flat_id' => $flat_id?->id,
                    'invoicePeriod' => $notice['invoicePeriod'],
                    'owner_association_id' => $oam_id?->owner_association_id,
                ],
                [
                    'legalNoticeId' => $notice['legalNoticeId'],
                    'mollakPropertyId' => $notice['mollakPropertyId'],
                    'registrationDate' => $notice['registrationDate'],
                    'registrationNumber' => $notice['registrationNumber'],
                    'previousBalance' => $notice['previousBalance'],
                    'invoiceAmount' => $notice['invoiceAmount'],
                    'approvedLegalAmount' => $notice['approvedLegalAmount'],
                    'legalNoticePDF' => $notice['legalNoticePDF'],
                    'isRDCCaseStart' => $notice['isRDCCaseStart'],
                    'isRDCCaseEnd' => $notice['isRDCCaseEnd']
                ]);
            }
        }
        } catch (\Exception $e) {
            Log::error('Failed to fetch legal notice issued ');
        }
    }
}
