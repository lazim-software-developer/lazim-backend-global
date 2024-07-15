<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Snowfire\Beautymail\Beautymail;

class SaleNocMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $user, protected $file, protected $emailCredentials)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.sale_noc_doc', ['user' => $this->user], function($message) {
            $fileName =  reset($this->file);
            $message
                ->from($this->emailCredentials,env('MAIL_FROM_NAME'))
                ->to($this->user->email, $this->user->first_name)
                ->subject('Welcome to Lazim!')
                ->attach(env('AWS_URL').'/'.$fileName );
        });
    }
}
