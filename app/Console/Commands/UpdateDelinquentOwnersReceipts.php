<?php

namespace App\Console\Commands;

use App\Jobs\UpdateDelinquentOwnerReceiptJob;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\DelinquentOwner;
use App\Models\FlatOwners;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateDelinquentOwnersReceipts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-delinquent-owners-receipts';

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
        $receipts = OAMReceipts::where('processed',0)->get();
        foreach ($receipts as $receipt){
            dispatch(new UpdateDelinquentOwnerReceiptJob($receipt));
        }
    }
}
