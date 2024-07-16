<?php

namespace App\Jobs;

use Parsedown;
use Illuminate\Bus\Queueable;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class OwnerMeeting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user;
    public $meeting;
    public $agenda;
    public $meetingsummary;
    public function __construct($user, $meeting, $agendaHtml,$meetingSummaryHtml,protected $emailCredentials)
    {
        $this->user = $user;
        $this->meeting = $meeting;
        $this->agenda = $agendaHtml;
        $this->meetingsummary = $meetingSummaryHtml;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.aftermeeting', ['user' => $this->user, 'meeting' => $this->meeting, 'agenda' => $this->agenda, 'meeting_summary' => $this->meetingsummary], function ($message) {
            $message
                ->from($this->emailCredentials,env('MAIL_FROM_NAME'))
                ->to($this->user->email, $this->user->first_name)
                ->subject('Owner Committe Meeting');
        });
    }
}
