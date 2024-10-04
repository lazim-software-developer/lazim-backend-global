<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class SendSubcontractorCreatedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $subContractor)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.subContractorCreated', ['subContractor' => $this->subContractor], function ($message) {
            $message
                ->to($this->subContractor->email, $this->subContractor->name)
                ->subject('Welcome to Lazim!');
        });

    }
}
