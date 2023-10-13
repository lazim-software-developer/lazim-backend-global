<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Snowfire\Beautymail\Beautymail;

class SendVerificationOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        // Generate OTPs
        $emailOtp = rand(1000, 9999);
        $phoneOtp = rand(1000, 9999);

        // Store OTPs in the database
        DB::table('otp_verifications')->insert([
            'otp' => $emailOtp,
            'type' => 'email',
            'contact_value' => $this->user->email,
        ]);

        DB::table('otp_verifications')->insert([
            'otp' => $phoneOtp,
            'type' => 'phone',
            'contact_value' => $this->user->phone,
        ]);

        // Send the email with the OTPs
        $data = [
            'emailOtp' => $emailOtp,
            'phoneOtp' => $phoneOtp,
        ];

        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.verification', ['user' => $this->user, 'data' => $data], function($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('OTP Verification');
        });
    }
}
