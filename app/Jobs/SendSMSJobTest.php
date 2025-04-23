<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class SendSMSJobTest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $phone;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct($phone, $message)
    {
        $this->phone = $phone;
        $this->message = urlencode($message); // Ensure URL encoding for safe transmission
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (env('APP_ENV') === 'production') {
            $response = Http::withOptions(['verify' => false])->withHeaders([
                'content-type' => 'application/json',
            ])->post(env("SMS_LINK") . "otpgenerate?username=" . env("SMS_USERNAME") .
                "&password=" . env("SMS_PASSWORD") . "&msisdn=" . $this->phone .
                "&msg=" . $this->message . "&source=ILAJ-LAZIM&tagname=" .
                env("SMS_TAG") . "&otplen=5&exptime=60");

            Log::info('SMS Response: ' . $response);
        }
    }
}
