<?php

namespace App\Console\Commands;

use App\Jobs\UpdateDelinquentOwnerInvoiceJob;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use App\Models\Building\Building;
use App\Models\DelinquentOwner;
use App\Models\FlatOwners;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateDelinquentOwners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-delinquent-owners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating delinquent owners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoices = OAMInvoice::where('invoice_due_date','<',Carbon::now()->todateString())->where('processed',0)->get();
        foreach ($invoices as $invoice) {
            dispatch(new UpdateDelinquentOwnerInvoiceJob($invoice));
        }

        $this->info('Delinquent owners have been updated.');
    }
}
