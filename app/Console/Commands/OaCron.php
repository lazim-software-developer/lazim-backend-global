<?php

namespace App\Console\Commands;

use App\Jobs\MailSendingJob;
use App\Models\Master\Role;
use App\Models\OaDetails;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OaCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oa:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::withOptions(['verify' => false])->withHeaders([
            'accept' => 'application/json',
        ])->get('https://qagate.dubailand.gov.ae/mollak/external/sync/managementcompany', [
            'consumer-id' => '8OSkYHBE5K7RS8oDfrGStgHJhhRoS7K9',
        ]);

          $data = json_decode($response);

        $oa = $data->response->managementCompanies;
        foreach ($oa as $company) {

            if (!OaDetails::where('oa_id', $company->id)->exists()) {
                $password = Str::random(12);

                $user = User::firstorcreate([
                    'first_name' => $company->name->englishName,
                    'email'      => $company->email,
                    'phone'      => $company->contactNumber,
                    'role_id'    => Role::where('name', 'OA')->value('id'),
                    'password'   => Hash::make($password),
                    'active'     => true,
                ]);
                MailSendingJob::dispatch($user,$password);
            }
        }
    }

}
