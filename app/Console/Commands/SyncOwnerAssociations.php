<?php

namespace App\Console\Commands;

use App\Jobs\FetchBuildingsJob;
use App\Models\OwnerAssociation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            try {
                $url = 'api/register';
                $body = [
                    'name'      => $company['name']['englishName'],
                    'email'     => $company['email'],
                    'password'  => '',
                    'password_confirmation' => '',
                    'created_by_lazim' => 1,
                    'owner_association_id' => 1,
                ];
                $httpRequest  = Http::withOptions(['verify' => false])
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                    ]);
                $response = $httpRequest->post(env('ACCOUNTING_URL') . $url, $body);
                Log::info([$response->json()]);
            } catch (\Exception $e) {
                Log::error('Error ' . $e->getMessage());
            }
            return $response->json();
            $connection = DB::connection('lazim_accounts');
            $connection->table('users')->insert([
                'name' => $company['name']['englishName'],
                'email'                => $company['email'],
                'email_verified_at' => now(),
                'type' => 'company',
                'lang' => 'en',
                'created_by' => 1,
                'plan' => 1,
                'owner_association_id' => $ownerAssociation?->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $accountUser = $connection->table('users')->where('email', $company['email'])->where('owner_association_id', $ownerAssociation?->id)->first();
            $role = $connection->table('roles')->where('name', 'company')->first();
            $connection->table('model_has_roles')->insert([
                'role_id' => $role?->id,
                'model_type' => 'App\Models\User',
                'model_id' => $accountUser?->id,
            ]);

            FetchBuildingsJob::dispatch($ownerAssociation);
        }
    }
}
