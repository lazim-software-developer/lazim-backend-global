<?php

namespace App\Console\Commands;

use App\Jobs\RiskPolicyExpiryMailJob;
use App\Models\AccountCredentials;
use App\Models\Building\Document;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Carbon\Carbon;
use Filament\Facades\Filament;
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
        foreach ($documents as $document) {
            $vendor           = Vendor::find($document->documentable_id);
            $user             = User::find($vendor->owner_id);
            $tenant           = Filament::getTenant()?->id ?? $document?->owner_association_id;
            // $emailCredentials = OwnerAssociation::find($tenant)?->accountcredentials()->where('active', true)->latest()->first()?->email ?? env('MAIL_FROM_ADDRESS');
            $credentials = AccountCredentials::where('oa_id', $tenant)->where('active', true)->latest()->first();
            $mailCredentials = [
                'mail_host' => $credentials->host??env('MAIL_HOST'),
                'mail_port' => $credentials->port??env('MAIL_PORT'),
                'mail_username'=> $credentials->username??env('MAIL_USERNAME'),
                'mail_password' => $credentials->password??env('MAIL_PASSWORD'),
                'mail_encryption' => $credentials->encryption??env('MAIL_ENCRYPTION'),
                'mail_from_address' => $credentials->email??env('MAIL_FROM_ADDRESS'),
            ];
            RiskPolicyExpiryMailJob::dispatch($user, $document, $mailCredentials);
        }
    }
}
