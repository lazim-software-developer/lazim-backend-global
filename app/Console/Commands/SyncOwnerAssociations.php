<?php

namespace App\Console\Commands;

use App\Jobs\FetchBuildingsJob;
use App\Models\OwnerAssociation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOwnerAssociations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:owner-associations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync owner associations from Mollak API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info("SyncOwnerAssociations executed", []);

        $response = Http::withoutVerifying()->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . '/sync/managementcompany');

        $managementCompanies = $response->json()['response']['managementCompanies'];

        foreach ($managementCompanies as $company) {
            $ownerAssociation = OwnerAssociation::firstOrCreate(
                [
                    'mollak_id' => $company['id'],
                    'trn_number' => $company['trn']
                ],
                [
                    'name'       => $company['name']['englishName'],
                    'phone'      => $company['contactNumber'],
                    'email'      => $company['email'],
                    'trn_number' => $company['trn'],
                    'address'    => $company['address'],
                ]
            );

            FetchBuildingsJob::dispatch($ownerAssociation);
        }
    }
}
