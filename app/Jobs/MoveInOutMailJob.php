<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Snowfire\Beautymail\Beautymail;

class MoveInOutMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $moveInOut;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $moveInOut, protected $mailCredentials)
    {
        $this->user = $user;
        $this->moveInOut = $moveInOut;
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

        $beautymail->send('emails.send_moveinout', [
            'user' => $this->user,
            'ticket_number' => $this->moveInOut->ticket_number,
            'building' => $this->moveInOut->building->name,
            'flat' => $this->moveInOut->flat->property_number,
            'type' => $this->moveInOut->type,
            'moving_date' => date("d-M-Y", strtotime($this->moveInOut->moving_date)),
            'moving_time' => date("d-M-Y", strtotime($this->moveInOut->moving_time)),
        ], function ($message) {
            $message
                ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
                ->to($this->user->email, $this->user->first_name)
                ->subject(ucwords($this->moveInOut->type) . ' Request Submitted');
        });
        Artisan::call('queue:restart');
    }
}
