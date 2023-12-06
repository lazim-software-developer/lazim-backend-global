<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class VendorRejectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $user;
    public $remarks;
    /**
     * Create a new job instance.
     */
    public function __construct($user,$remarks)
    {
        $this->user     = $user;
        $this->remarks = $remarks;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.vendor-rejection', ['user' => $this->user,'remarks' => $this->remarks], function($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Welcome to Lazim!');
        });
    }
}
