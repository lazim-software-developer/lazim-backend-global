<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class SendInactiveStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        logger()->info('User data:', [
            'name'  => $this->user->name,
            'email' => $this->user->email,
        ]);

        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.inactive_status',
            ['user' => $this->user],
            function ($message) {
                $message
                    ->to($this->user->email, $this->user->name)
                    ->subject('Account Deactivated!');
            });
    }
}
