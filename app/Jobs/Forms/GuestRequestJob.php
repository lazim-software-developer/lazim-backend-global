<?php

namespace App\Jobs\Forms;

use Illuminate\Bus\Queueable;
use App\Models\OwnerAssociation;
use Illuminate\Support\Facades\DB;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GuestRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $dataObj;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $dataObj, protected $mailCredentials)
    {
        $this->user = $user;
        $this->dataObj = $dataObj;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Config::set('mail.mailers.smtp.host', $this->mailCredentials['mail_host']);
        Config::set('mail.mailers.smtp.port', $this->mailCredentials['mail_port']);
        Config::set('mail.mailers.smtp.username', $this->mailCredentials['mail_username']);
        Config::set('mail.mailers.smtp.password', $this->mailCredentials['mail_password']);
        Config::set('mail.mailers.smtp.encryption', $this->mailCredentials['mail_encryption']);
        Config::set('mail.mailers.smtp.email', $this->mailCredentials['mail_from_address']);

        $oaId = DB::table('building_owner_association')
            ->where(['building_id' => $this->dataObj->building_id, 'active' => 1])->first()?->owner_association_id;
        
        $ownerAssociation = OwnerAssociation::where('id', $oaId)->first();
        $property_manager_name = $ownerAssociation?->name;
        $property_manager_logo = $ownerAssociation?->profile_photo;
        
        // Add AWS URL to logo path if logo exists
        $property_manager_logo = $property_manager_logo ? env('AWS_URL') . '/' . $property_manager_logo : null;

        $beautymail = app()->make(Beautymail::class);

        $beautymail->send('emails.forms.guest_form_request', [
            'user' => $this->user,
            'ticket_number' => $this->dataObj->ticket_number,
            'building' => $this->dataObj->building->name,
            'flat' => $this->dataObj->flat->property_number,
            'type' => 'Guest registration',
            'property_manager_name' => $property_manager_name,
            'property_manager_logo' => $property_manager_logo
        ], function ($message) {
            $message
                ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
                ->to($this->user->email, $this->user->first_name)
                ->subject('Guest Registration Request Acknowledgment');
        });
        Artisan::call('queue:restart');
    }
}
