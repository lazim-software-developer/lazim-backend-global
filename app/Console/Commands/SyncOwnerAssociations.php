<?php

namespace App\Console\Commands;

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
        $response = Http::withoutVerifying()->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID", "dqHdShhrZQgeSY9a4BZh6cgucpQJvS5r"),
        ])->get(env("MOLLAK_API_URL", "https://b2bgateway.dubailand.gov.ae/mollak/external") . '/sync/managementcompany');

        $managementCompanies = $response->json()['response']['managementCompanies'];

        Log::info("Sync Owner Association", [1]);

        foreach ($managementCompanies as $company) {
            OwnerAssociation::firstOrCreate(
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
        }
    }
}
