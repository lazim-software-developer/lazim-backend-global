<?php
namespace App\Console\Commands;

use App\Models\RentalDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckRentalContracts extends Command
{
    protected $signature   = 'rental:check-contracts';
    protected $description = 'Check rental contracts and update status if contract_end_date is less than today';

    public function handle()
    {
        $today = Carbon::today();
        RentalDetail::where('contract_end_date', '<', $today)
            ->update(['status' => 'Contract ended']);

        $this->info('Rental contracts checked and updated successfully.');
    }
}
