<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class Residentapproval implements ShouldQueue
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
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.Residentapproval', ['user' => $this->user], function ($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Welcome to Lazim!');
        });
    }
}
