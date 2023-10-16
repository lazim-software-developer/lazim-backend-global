<?php

namespace App\Jobs\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class ResendOtpEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $otp;
    protected $type;

    public function __construct($user, $otp, $type)
    {
        $this->user = $user;
        $this->otp = $otp;
        $this->type = $type;
    }

    public function handle()
    {
        $data = [
            'emailOtp' => $this->type == 'email' ? $this->otp : '',
            'phoneOtp' => $this->type == 'phone' ? $this->otp : '',
        ];

        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.verification', ['user' => $this->user, 'data' => $data], function($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('OTP Verification');
        });
    }
}
