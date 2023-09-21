<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\OaUserRegistration;
use Snowfire\Beautymail\Beautymail;

class AccountCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->user=$user;
        $this->password=$password;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.oa-user_registration', ['user' => $this->user, 'password' => $this->password], function($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Welcome to Lazim!');
        });
    }
}
