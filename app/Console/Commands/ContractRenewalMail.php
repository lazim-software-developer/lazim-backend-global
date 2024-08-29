<?php

namespace App\Console\Commands;

use App\Jobs\ContractRenewalJob;
use App\Models\AccountCredentials;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Console\Command;

class ContractRenewalMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:contract-renewal-mail';

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
        $contracts = Contract::where('end_date', '<', Carbon::now()->addDays(30))->where('end_date', '>', Carbon::now())->get();
        foreach ($contracts as $contract) {
            $vendor           = Vendor::find($contract->vendor_id)->owner_id;
            $user             = User::find($vendor);
            $tenant           = Filament::getTenant()?->id ?? $contract->owner_association_id;
            // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');
            $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host' => $credentials->host??env('MAIL_HOST'),
                'mail_port' => $credentials->port??env('MAIL_PORT'),
                'mail_username'=> $credentials->username??env('MAIL_USERNAME'),
                'mail_password' => $credentials->password??env('MAIL_PASSWORD'),
                'mail_encryption' => $credentials->encryption??env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email??env('MAIL_FROM_ADDRESS'),
            ];

            ContractRenewalJob::dispatch($contract, $user, $mailCredentials);

        }
    }
}
