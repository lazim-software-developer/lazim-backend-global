<?php

namespace App\Console\Commands;

use App\Jobs\AccountCreationJob;
use App\Models\Master\Role;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
                ['mollak_id' => $company['id']],
                [
                    'name'       => $company['name']['englishName'],
                    'phone'      => $company['contactNumber'],
                    'email'      => $company['email'],
                    'trn_number' => $company['trn'],
                    'address'    => $company['address'],
                ]


            );
            $ownerAssociationId = OwnerAssociation::where('mollak_id', $company['id'])->value('id');
            $password = Str::random(12);
            $user     = User::firstorcreate([
                'first_name' => $company['name']['englishName'],
                'email'      =>  $company['email'],
                'phone'      =>$company['contactNumber'],
                'role_id'    => Role::where('name', 'OA')->value('id'),
                'password'   => Hash::make($password),
                'active'     => true,
                'owner_association_id'=> $ownerAssociationId
            ]);
            AccountCreationJob::dispatch($user, $password);

        }
    }
}
