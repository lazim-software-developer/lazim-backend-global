<?php

namespace App\Console\Commands;

use App\Models\OaUserRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class OAAccountCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lazim:oa-account-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create user account and send the mail to user ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::withoutVerifying()->withHeaders([
            'content-type' => 'application/json',
            'consumer-id'  => env("MOLLAK_CONSUMER_ID"),
        ])->get(env("MOLLAK_API_URL") . '/sync/managementcompany');

        $data            = json_decode($response);
        $company_details = $data->response->managementCompanies;
        foreach ($company_details as $company) {

            OaUserRegistration::firstorcreate([
                'oa_id'   => $company->id,
                'name'    => $company->name->englishName,
                'email'   => $company->email,
                'phone'   => $company->contactNumber,
                'trn'     => $company->trn,
                'address' => $company->address,

            ]);

        }

    }

}
