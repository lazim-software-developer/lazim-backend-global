<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class TLExpiryMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user;
    public $vendor;
    public function __construct($user, $vendor)
    {
        $this->user = $user;
        $this->vendor = $vendor;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.t_l_expiry_mail', ['user' => $this->user,'vendor' => $this->vendor], function($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Welcome to Lazim!');
        });
    }
}
