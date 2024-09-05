<?php

namespace App\Jobs;

use App\Models\Building\Flat;
use App\Models\LegalNotice;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncLegalNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $building)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $propertyGroupId = $this->building->property_group_id;
        $url = env("MOLLAK_API_URL") ."/sync/legalnotice/". $propertyGroupId;
        try{

            $response = Http::withoutVerifying()->retry(2, 500)->timeout(60)->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
                ])->get($url);

            $properties = $response->json()['response']['propertyGroups'];

            foreach($properties as $property){
                foreach ($property['mollakProperties'] as $legalNotice) {
                    $flat = Flat::where('mollak_property_id', $legalNotice['mollakPropertyId'])->first();
                    LegalNotice::firstOrCreate([
                        'legalNoticeId' => $legalNotice['$legalNotice']
                    ],[
                        'building_id' => $this->building->id,
                        'flat_id' => $flat?->id,
                        'owner_association_id' => $flat?->owner_association_id,
                        'mollakPropertyId' => $legalNotice['mollakPropertyId'],
                        'registrationDate' => $legalNotice['registrationDate'],
                        'registrationNumber' => $legalNotice['registrationNumber'],
                        'invoiceNumber' => $legalNotice['invoiceNumber'],
                        'invoicePeriod' => $legalNotice['invoicePeriod'],
                        'previousBalance' => $legalNotice['previousBalance'],
                        'invoiceAmount' => $legalNotice['invoiceAmount'],
                        'approvedLegalAmount' => $legalNotice['approvedLegalAmount'],
                        'legalNoticePDF' => $legalNotice['legalNoticePDF'],
                        'isRDCCaseStart' => $legalNotice['isRDCCaseStart'],
                        'isRDCCaseEnd' => $legalNotice['isRDCCaseEnd'],
                        'due_date' => (new DateTime($legalNotice['registrationDate']))->modify('+30 days')->format('Y-m-d'),
                    ]);
                }
            }

        }
        catch (\Exception $e) {
            Log::error("Legal notice Fetch Failed: ".$e->getMessage());
            Log::error('Failed to fetch or save legal Notice: ' . $this->building->property_group_id);
        }
    }
}
