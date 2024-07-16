<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class WelcomeNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $email;
    public $name;
    public $building;
    public function __construct($email,$name,$building,protected $emailCredentials)
    {
        $this->email = $email;
        $this->name = $name;
        $this->building = $building;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.Welcomenotification', ['building' => $this->building,'name' => $this->name], function ($message) {
            $message
                ->from($this->emailCredentials,env('MAIL_FROM_NAME'))
                ->to($this->email, $this->name)
                ->subject('Symbiosis Owner Association Management Services- Welcome to Lazim!');
        });
    }
}
