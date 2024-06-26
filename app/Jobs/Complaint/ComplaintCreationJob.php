<?php

namespace App\Jobs\Complaint;

use App\Models\Building\Complaint;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class ComplaintCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $complaintId, protected $technicianId = null)
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $beautymail = app()->make(Beautymail::class);
        $dataObj = Complaint::findOrFail($this->complaintId);

        if($this->technicianId){
            $this->sendMailToTechnician($this->technicianId, $beautymail, $dataObj);
        } else {
            $user = $dataObj->user;

            $beautymail->send('emails.complaint.complaint_request_submitted', [
                'user' => $user,
                'ticket_number' => $dataObj->ticket_number,
                'building' => $dataObj->building->name,
                'flat' => $dataObj->flat->property_number,
                'type' => 'Complaint'
            ], function ($message) use ($user) {
                $message
                    ->to($user->email, $user->first_name)
                    ->subject('Complaint Request Submitted');
            });
        }

    }

    public function sendMailToTechnician($technicianId, $beautymail, $dataObj){
        $user = User::findOrFail($technicianId);

        $beautymail->send('emails.complaint.complaint_to_technician', [
            'user' => $user,
            'ticket_number' => $dataObj->ticket_number,
            'building' => $dataObj->building->name,
            'flat' => $dataObj->flat->property_number,
            'type' => 'Task Assigned'
        ], function ($message) use ($user) {
            $message
                ->to($user->email, $user->first_name)
                ->subject('Task Assigned');
        });
    }
}
