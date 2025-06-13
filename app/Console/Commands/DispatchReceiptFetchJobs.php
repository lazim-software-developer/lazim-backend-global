<?php

namespace App\Console\Commands;

use App\Jobs\OAM\FetchAndSaveReceipts;
use App\Models\Building\Building;
use Illuminate\Console\Command;

class DispatchReceiptFetchJobs extends Command
{
    protected $signature = 'dispatch:receipt-fetch';
    protected $description = 'Dispatch jobs to fetch and save receipts for each building';

    public function handle()
    {
        $buildings = Building::where('managed_by','OA')->get();
        if ($buildings->isEmpty()) {
            $this->info('No buildings found to fetch receipts for.');
            return;
        }
        // $buildings = Building::all();
        foreach ($buildings as $building) {
            dispatch(new FetchAndSaveReceipts($building));
        }

        $this->info('Receipt fetch jobs dispatched for all buildings.');
    }
}
