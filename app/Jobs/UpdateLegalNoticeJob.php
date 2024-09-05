<?php

namespace App\Jobs;

use App\Models\Building\Building;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateLegalNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $legalNotice)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $building = Building::find($this->legalNotice->building_id);
        $url = env("MOLLAK_API_URL") . "/sync/legalnotice/" .$building->property_group_id.'/'. $this->legalNotice->mollakPropertyId.'/'.$this->legalNotice->registrationNumber.'/rdcdetail';
        try {

            $response = Http::withoutVerifying()->retry(2, 500)->timeout(60)->withHeaders([
                'content-type' => 'application/json',
                'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
            ])->get($url);

            $data = $response->json()['response'];

            $this->legalNotice->case_status = $data['caseStatus']['englishName'];
            $this->legalNotice->case_number = $data['caseNo'];
            $this->legalNotice->case_type = $data['caseType']['englishName'];
            $this->legalNotice->save();

        } catch (\Exception $e) {
            Log::error("Legal notice update Failed: " . $e->getMessage());
        }
    }
}
