<?php

namespace App\Console\Commands;

use App\Jobs\TLExpiryMailJob;
use App\Models\AccountCredentials;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Filament\Facades\Filament;
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
            $tenant = Filament::getTenant()?->id ?? $vendor->ownerAssociation->where('active',true)->first()?->owner_association_id;
            $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host' => $credentials->host??env('MAIL_HOST'),
                'mail_port' => $credentials->port??env('MAIL_PORT'),
                'mail_username'=> $credentials->username??env('MAIL_USERNAME'),
                'mail_password' => $credentials->password??env('MAIL_PASSWORD'),
                'mail_encryption' => $credentials->encryption??env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email??env('MAIL_FROM_ADDRESS'),
            ];
            TLExpiryMailJob::dispatch($user, $vendor, $mailCredentials);
        }
    }
}
