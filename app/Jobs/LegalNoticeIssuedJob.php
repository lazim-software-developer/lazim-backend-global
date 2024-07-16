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
        $results = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get("https://qagate.dubailand.gov.ae/mollak/external/sync/legalnotice/".$propertyGroupId."/".$mollakPropertyId."/".$legalNoticeId);
        
        $responce = $results->json()['response']['propertyGroups'];
        $building_id = Building::where('mollak_property_id',$propertyGroupId)->first();
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
}
