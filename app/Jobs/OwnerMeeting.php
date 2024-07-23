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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

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
    public function __construct($user, $meeting, $agendaHtml,$meetingSummaryHtml,protected $mailCredentials)
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
        Config::set('mail.mailers.smtp.host', $this->mailCredentials['mail_host']);
        Config::set('mail.mailers.smtp.port', $this->mailCredentials['mail_port']);
        Config::set('mail.mailers.smtp.username', $this->mailCredentials['mail_username']);
        Config::set('mail.mailers.smtp.password', $this->mailCredentials['mail_password']);
        Config::set('mail.mailers.smtp.encryption', $this->mailCredentials['mail_encryption']);
        Config::set('mail.mailers.smtp.email', $this->mailCredentials['mail_from_address']);
        
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.aftermeeting', ['user' => $this->user, 'meeting' => $this->meeting, 'agenda' => $this->agenda, 'meeting_summary' => $this->meetingsummary], function ($message) {
            $message
                ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
                ->to($this->user->email, $this->user->first_name)
                ->subject('Owner Committe Meeting');
        });

        Artisan::call('queue:restart');
    }
}
