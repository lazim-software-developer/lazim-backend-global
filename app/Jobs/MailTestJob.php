<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Snowfire\Beautymail\Beautymail;

class MailTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $credentials)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        Config::set('mail.mailers.smtp.username', $this->credentials->email);
        Config::set('mail.mailers.smtp.password', $this->credentials->password);
        
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.mail-testing', ['user' => ''], function ($message) {
            $message
                ->from('one@gmail.com')
                ->to('owner@gmail.com', 'owner')
                ->subject('Welcome to Lazim!');
        });

        Artisan::call('queue:restart');
    }
}
