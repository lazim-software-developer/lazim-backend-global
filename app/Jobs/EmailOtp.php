<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class EmailOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $otp, protected $type, protected $email)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data = [
            'emailOtp' => $this->type == 'email' ? $this->otp : '',
        ];

        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.email-otp', ['data' => $data], function($message) {
            $message
                ->to($this->email)
                ->subject('OTP Verification');
        });
    }
}
