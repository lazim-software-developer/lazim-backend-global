<?php

namespace App\Console\Commands;

use App\Jobs\ContractRenewalJob;
use App\Jobs\ContractRenewalMailJob;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Contract;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        foreach($contracts as $contract){
            $vendor = Vendor::find($contract->vendor_id)->owner_id;
            $user = User::find($vendor);
            $tenant           = Filament::getTenant()?->id ?? auth()->user()->owner_association_id;
            $emailCredentials = OwnerAssociation::find($tenant)->accountcredentials()->where('active', true)->latest()->first()->email ?? env('MAIL_FROM_ADDRESS');

            ContractRenewalJob::dispatch($contract, $user, $emailCredentials);

        }
    }
}
