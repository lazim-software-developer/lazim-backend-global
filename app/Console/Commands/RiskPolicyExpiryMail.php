<?php

namespace App\Console\Commands;

use App\Jobs\RiskPolicyExpiryMailJob;
use App\Models\Building\Document;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RiskPolicyExpiryMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:risk-policy-expiry-mail';

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
        // $vendors = Vendor::where('tl_expiry', '<', Carbon::now()->addDays(30))->where('tl_expiry', '>', Carbon::now())->get();
        $documents = Document::where('name', 'risk_policy')->where('expiry_date', '<', Carbon::now()->addDays(30))->where('expiry_date', '>', Carbon::now())->get();
        foreach($documents as $document){
            $vendor = Vendor::find($document->documentable_id);
            $user = User::find($vendor->owner_id);
            RiskPolicyExpiryMailJob::dispatch($user, $document);
        }
    }
}
