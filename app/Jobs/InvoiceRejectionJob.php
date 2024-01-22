<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class InvoiceRejectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user;
    public $remarks;
    public $invoice;
    public function __construct($user, $remarks, $invoice)
    {
        $this->user = $user;
        $this->remarks = $remarks;
        $this->invoice = $invoice;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.Invoicerejection', ['user' => $this->user, 'remarks' => $this->remarks, 'invoice' => $this->invoice], function ($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Invoice Rejection');
        });
    }
}
