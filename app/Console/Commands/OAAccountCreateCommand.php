<?php

namespace App\Console\Commands;

use App\Jobs\AccountCreationJob;
use App\Models\Master\Role;
use App\Models\OaDetails;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

        $data = json_decode($response);

        $company_details = $data->response->managementCompanies;
        foreach ($company_details as $company) {

            if (!OaDetails::where('oa_id', $company->id)->exists()) {
                $password = Str::random(12);
                $user     = User::firstorcreate([
                    'first_name' => $company->name->englishName,
                    'email'      => $company->email,
                    'phone'      => $company->contactNumber,
                    'role_id'    => Role::where('name', 'OA')->value('id'),
                    'password'   => Hash::make($password),
                    'active'     => true,
                ]);
                AccountCreationJob::dispatch($user, $password);
                OaDetails::firstorcreate([
                    'oa_id'   => $company->id,
                    'user_id' => User::where('first_name', $company->name->englishName)->value('id'),
                ]);

            }
        }
    }

}
