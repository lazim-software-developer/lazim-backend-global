<?php

namespace App\Jobs;

use App\Models\Building\Building;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchBuildingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ownerAssociation;

    /**
     * Create a new job instance.
     */
    public function __construct($ownerAssociation)
    {
        $this->ownerAssociation = $ownerAssociation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . "/sync/managementcompany/" . $this->ownerAssociation->mollak_id . "/propertygroups");
    

        // Save buildings to Building table 
        if ($response->successful()) {
            $propertyGroups = $response->json()['response']['propertyGroups'];

            foreach ($propertyGroups as $group) {
                Building::firstOrCreate(
                    ['property_group_id' => $group['propertyGroupId']],
                    [
                        'name' => $group['propertyGroupName']['englishName'],
                        'owner_association_id' => $this->ownerAssociation->id,
                        'merchant_code' => $group['merchantCode'],
                        'address_line1' => $group['projectName']['englishName'],
                    ]
                );
            }
        }
    }
}
