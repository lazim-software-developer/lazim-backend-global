<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class BuildingSecurity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user;
    public $password;
    public function __construct($user, $password, protected $mailCredentials)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Config::set('mail.mailers.smtp.mailer', $this->mailCredentials['mailer']);
        Config::set('mail.mailers.smtp.host', $this->mailCredentials['host']);
        Config::set('mail.mailers.smtp.port', $this->mailCredentials['port']);
        Config::set('mail.mailers.smtp.username', $this->mailCredentials['username']);
        Config::set('mail.mailers.smtp.password', $this->mailCredentials['password']);
        Config::set('mail.mailers.smtp.encryption', $this->mailCredentials['encryption']);
        Config::set('mail.mailers.smtp.email', $this->mailCredentials['email']);

        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.buildingsecurity', ['user' => $this->user, 'password' => $this->password], function ($message) {
            $message
                ->from($this->mailCredentials['email'],env('MAIL_FROM_NAME'))
                ->to($this->user->email, $this->user->first_name)
                ->subject('Welcome to Lazim!');
        });

        Artisan::call('queue:restart');
    }
}
