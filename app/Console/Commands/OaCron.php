<?php

namespace App\Console\Commands;

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

        $oa       = $data->response->managementCompanies[0];
        $password = Str::random(12);
        $data     = OaDetails::where('oa_id', $oa->id)->first();
        if (!$data) {
            User::firstOrCreate([
                'first_name' => $oa->name->englishName,
                'email'      => $oa->email,
                'phone'      => $oa->contactNumber,
                'role_id'    => Role::where('name', 'OA')->value('id'),
                'password'   => Hash::make($password),
                'active'     => true,
            ]);
            Mail::send('emails.oa-user_registration', ['name' => $oa->name->englishName, 'username' => $oa->email, 'password' => $password],
                function ($message) use ($oa) {

                    $message->to($oa->email);

                    $message->subject('Password ');

                });

        }

    }

}
