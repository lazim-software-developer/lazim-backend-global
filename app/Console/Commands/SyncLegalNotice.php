<?php

namespace App\Console\Commands;

use App\Jobs\SyncLegalNoticeJob;
use App\Models\Building\Building;
use Illuminate\Console\Command;

class SyncLegalNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-legal-notice';

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
        Building::chunk(100, function ($buildings) {
            foreach ($buildings as $building) {
                dispatch(new SyncLegalNoticeJob($building));
            }
        });
    }
}
