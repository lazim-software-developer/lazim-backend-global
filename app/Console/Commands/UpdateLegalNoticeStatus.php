<?php

namespace App\Console\Commands;

use App\Jobs\UpdateLegalNoticeJob;
use App\Models\LegalNotice;
use Illuminate\Console\Command;

class UpdateLegalNoticeStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-legal-notice-status';

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
        $legalNotices = LegalNotice::whereYear('due_date', now()->year)->whereNot('case_status','Closed')->get();
        foreach($legalNotices as $legalNotice){
            UpdateLegalNoticeJob::dispatch($legalNotice);
        }
    }
}
