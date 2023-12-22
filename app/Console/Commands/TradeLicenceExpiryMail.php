<?php

namespace App\Console\Commands;

use App\Jobs\TLExpiryMailJob;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TradeLicenceExpiryMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:trade-licence-expiry-mail';

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
        $vendors = Vendor::where('tl_expiry', '<', Carbon::now()->addDays(30))->where('tl_expiry', '>', Carbon::now())->get();
        foreach($vendors as $vendor){
            $user = User::find($vendor->owner_id);
            TLExpiryMailJob::dispatch($user, $vendor);
        }
    }
}
