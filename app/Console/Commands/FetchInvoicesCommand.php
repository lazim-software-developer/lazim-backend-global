<?php

namespace App\Console\Commands;

use App\Jobs\OAM\FetchAndSaveInvoices;
use App\Models\Building\Building;
use Illuminate\Console\Command;

class FetchInvoicesCommand extends Command
{
    protected $signature = 'fetch:invoices';
    protected $description = 'Fetch invoices from external API and save to database';

    public function handle()
    {
        $delaySeconds = 0;
        $requestsPerMinute = 8;
        $delayPerRequest = 60 / $requestsPerMinute; // 7.5 seconds
        Building::where('managed_by','OA')->chunk(100, function ($buildings) use (&$delaySeconds, $delayPerRequest) {
            foreach ($buildings as $building) {
                dispatch(new FetchAndSaveInvoices($building))->delay(now()->addSeconds($delaySeconds));
                $delaySeconds += $delayPerRequest; // Increment delay by 3 seconds for the next job
            }
        });

        $this->info('The invoices have been fetched and saved successfully.');
    }
}
