<?php

namespace App\Jobs;

use App\Models\Building\Building;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Jobs\FetchFlatsAndOwnersForBuilding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                $building =  Building::firstOrCreate(
                    [
                        'property_group_id' => $group['propertyGroupId'],
                        'owner_association_id' => $this->ownerAssociation->id,
                    ],
                    [
                        'name' => $group['propertyGroupName']['englishName'],
                        'area' => $group['masterCommunityName']['englishName'],
                        'merchant_code' => $group['merchantCode'],
                        'address_line1' => $group['projectName']['englishName'],
                    ]
                );
                DB::table('building_owner_association')->updateOrInsert([
                    'owner_association_id' => $this->ownerAssociation->id,
                    'building_id' => $building->id,
                    'from' => now()->toDateString(),
                ]);

                $connection = DB::connection('lazim_accounts');
                $created_by = $connection->table('users')->where('owner_association_id', $this->ownerAssociation->id)->where('type', 'company')->first()?->id;
                $connection->table('users')->updateOrInsert([
                    'building_id' => $building->id,
                    'owner_association_id' => $this->ownerAssociation->id,
                ],[
                    'name' => $building->name,
                    'email' => 'user' . Str::random(8) . '@lazim.ae',
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'type' => 'building',
                    'lang' => 'en',
                    'created_by' => $created_by,
                    'is_disable' => 0,
                    'plan' => 1,
                    'is_enable_login' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                FetchFlatsAndOwnersForBuilding::dispatch($building);
            }
        }
    }
}
