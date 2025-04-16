<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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

        DB::table('otp_verifications')
        ->where('contact_value', $this->user->email)
        ->orWhere('contact_value', $this->user->phone)
        ->delete();

        // Store OTPs in the database
        DB::table('otp_verifications')->insert([
            ['type' => 'email', 'contact_value' => $this->user->email, 'otp' => $emailOtp],
            ['type' => 'phone', 'contact_value' => $this->user->phone, 'otp' => $phoneOtp]
        ]);

        // Send the email with the OTPs
        $data = [
            'emailOtp' => $emailOtp ?? '',
            'phoneOtp' => $phoneOtp ?? '',
        ];

        if(env('APP_ENV') == 'production'){
            $response = Http::withOptions(['verify' => false])->withHeaders([
                'content-type' => 'application/json',
            ])->post(env("SMS_LINK") . "otpgenerate?username=" . env("SMS_USERNAME") . "&password=" . env("SMS_PASSWORD") . "&msisdn=" . $this->user->phone . "&msg=Your%20one%20time%20OTP%20is%20%25m&source=ILAJ-LAZIM&tagname=" . env("SMS_TAG") . "&otplen=5&exptime=60");
    
            Log::info('RESPONSEEE:-' . $response);
    
        }

        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.verification', ['user' => $this->user, 'data' => $data], function($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('OTP Verification');
        });
    }
}
