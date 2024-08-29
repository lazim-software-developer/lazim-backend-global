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
        Building::chunk(100, function ($buildings) {
            foreach ($buildings as $building) {
                dispatch(new FetchAndSaveInvoices($building));
            }
        });

        $this->info('The invoices have been fetched and saved successfully.');
    }
}
