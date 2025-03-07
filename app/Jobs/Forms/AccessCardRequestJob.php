<?php

namespace App\Jobs\Forms;

use App\Models\OwnerAssociation;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class AccessCardRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $accessCard;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $accessCard, protected $mailCredentials)
    {
        $this->user = $user;
        $this->accessCard = $accessCard;
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
            ->where(['building_id' => $this->accessCard->building_id, 'active' => 1])->first()?->owner_association_id;
        
        $ownerAssociation = OwnerAssociation::where('id', $oaId)->first();
        $property_manager_name = $ownerAssociation?->name;
        $property_manager_logo = $ownerAssociation?->profile_photo;
        
        // Add AWS URL to logo path if logo exists
        $property_manager_logo = $property_manager_logo ? env('AWS_URL') . '/' . $property_manager_logo : null;

        $beautymail = app()->make(Beautymail::class);

        $beautymail->send('emails.forms.access_card_request', [
            'user' => $this->user,
            'ticket_number' => $this->accessCard->ticket_number,
            'building' => $this->accessCard->building->name,
            'flat' => $this->accessCard->flat->property_number,
            'type' => 'Access Card',
            'card_type' => $this->accessCard->card_type,
            'property_manager_name' => $property_manager_name,
            'property_manager_logo' => $property_manager_logo,
        ], function ($message) {
            $message
                ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
                ->to($this->user->email, $this->user->first_name)
                ->subject('Access Card Request Acknowledgment');
        });

        Artisan::call('queue:restart');
    }
}
