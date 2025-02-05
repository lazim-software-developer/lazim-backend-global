<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class PaymentRequestMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $user, protected $rentalCheque)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $requestedBy = $this->rentalCheque?->rentalDetail?->flatTenant?->user?->first_name;
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.request_payment_link', ['user' => $this->user, 'rentalCheque' => $this->rentalCheque, 'requestedBy' => $requestedBy], function ($message) use ($requestedBy) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Payment Link Request Against Cheque '.$this->rentalCheque?->cheque_number.' '.$requestedBy.'.');
        });
    }
}
