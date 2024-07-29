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

class FitOutContractorMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $name,protected $email,protected $form,protected $mailCredentials)
    {
        //
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
        $beautymail->send('emails.contractor-fitout-form', [
            'name' => $this->name,
            'email' => $this->email,
            'id' => $this->form->id,
            'ticket_number' => $this->form->ticket_number,
            'building' => $this->form->building->name,
            'flat' => $this->form->flat->property_number,
        ], function($message) {
            $message
                ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
                ->to($this->email, $this->name)
                ->subject('Fit-out Request Submitted');
        });

        Artisan::call('queue:restart');
    }
}
