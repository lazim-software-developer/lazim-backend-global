<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Snowfire\Beautymail\Beautymail;

class RejectedFMJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user;
    public $password;
    protected $mailCredentials;

    public function __construct($user, $password)
    {
        $this->user            = $user;
        $this->password        = $password;
        $this->mailCredentials = [
            'mail_host'         => config('mail.mailers.smtp.host'),
            'mail_port'         => config('mail.mailers.smtp.port'),
            'mail_username'     => config('mail.mailers.smtp.username'),
            'mail_password'     => config('mail.mailers.smtp.password'),
            'mail_encryption'   => config('mail.mailers.smtp.encryption'),
            'mail_from_address' => config('mail.from.address'),
        ];
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
        $beautymail->send('emails.rejectedfacilitymanager',
            ['user' => $this->user, 'password' => $this->password], function ($message) {
                $message
                    ->from($this->mailCredentials['mail_from_address'], env('MAIL_FROM_NAME'))
                    ->to($this->user->email, $this->user->first_name)
                    ->subject('Welcome to Lazim!');
            });

        Artisan::call('queue:restart');
    }
}
