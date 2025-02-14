<?php

namespace App\Console\Commands;

use App\Jobs\SendContractRenewalReminder;
use Illuminate\Console\Command;

class SubContractRenewalReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sub-contract-renewal-reminder';

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
        SendContractRenewalReminder::dispatch();
    }
}
