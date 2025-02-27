<?php

namespace App\Jobs\Complaint;

use App\Models\OwnerAssociation;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use App\Models\Building\Complaint;
use Illuminate\Support\Facades\DB;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ComplaintCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $complaintId, protected $technicianId = null, protected $mailCredentials)
    {
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

        $beautymail = app()->make(Beautymail::class);
        $dataObj = Complaint::findOrFail($this->complaintId);
        $oaId = DB::table('building_owner_association')
            ->where(['building_id' => $dataObj->building_id, 'active' => 1])->first()?->owner_association_id;

        $ownerAssociation = OwnerAssociation::where('id', $oaId)->first();
        $property_manager_name = $ownerAssociation?->name;
        $property_manager_logo = $ownerAssociation?->profile_photo;

        // Add AWS URL to logo path if logo exists
        $property_manager_logo = $property_manager_logo ? env('AWS_URL') . '/' . $property_manager_logo : null;

        if($this->technicianId){
            $this->sendMailToTechnician($this->technicianId, $beautymail, $dataObj, $property_manager_name, $property_manager_logo);
        } else {
            $user = $dataObj->user;

            $beautymail->send('emails.complaint.complaint_request_submitted', [
                'user' => $user,
                'ticket_number' => $dataObj->ticket_number,
                'building' => $dataObj->building->name,
                'flat' => $dataObj?->flat?->property_number ?? '',
                'property_manager_name' => $property_manager_name ?? '',
                'property_manager_logo' => $property_manager_logo ?? '',
            ], function ($message) use ($user) {
                $message
                    ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
                    ->to($user->email, $user->first_name)
                    ->subject('Complaint Request Acknowledgment');
            });
        }
        Artisan::call('queue:restart');

    }

    public function sendMailToTechnician($technicianId, $beautymail, $dataObj, $property_manager_name, $property_manager_logo){
        $user = User::findOrFail($technicianId);

        $beautymail->send('emails.complaint.complaint_to_technician', [
            'user' => $user,
            'ticket_number' => $dataObj->ticket_number,
            'building' => $dataObj->building->name,
            'flat' => $dataObj?->flat?->property_number ?? 'Not Applicable',
            'property_manager_name' => $property_manager_name ?? '',
            'property_manager_logo' => $property_manager_logo ?? '',
            'description' => $dataObj->complaint
        ], function ($message) use ($user, $dataObj) {
            $message
                ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
                ->to($user->email, $user->first_name)
                ->subject('New Task Assigned '.$dataObj?->flat?->property_number ?? ''.' '.$dataObj->building->name);
        });
    }
}
