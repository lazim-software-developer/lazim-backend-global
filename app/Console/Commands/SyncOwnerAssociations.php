<?php

namespace App\Console\Commands;

use App\Models\OwnerAssociation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

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
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . '/sync/managementcompany');

        $managementCompanies = $response->json()['response']['managementCompanies'];

        foreach ($managementCompanies as $company) {
            OwnerAssociation::firstOrCreate(
                [
                    'mollak_id' => $company['id'],
                    'trn_number' => $company['trn_number']
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
