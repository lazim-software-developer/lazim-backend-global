<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Building\Flat;
use App\Jobs\FetchOwnersForFlat;

class DispatchFetchOwnersJob extends Command
{
    protected $signature = 'job:fetch-owners {flatId}';
    protected $description = 'Dispatch the FetchOwnersForFlat job for a specific flat';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $flatId = $this->argument('flatId');
        $flat = Flat::find($flatId);

        if (!$flat) {
            $this->error("Flat with ID $flatId not found.");
            return 1;
        }

        FetchOwnersForFlat::dispatch($flat);
        $this->info("Job dispatched for Flat ID: $flatId");

        return 0;
    }
}
