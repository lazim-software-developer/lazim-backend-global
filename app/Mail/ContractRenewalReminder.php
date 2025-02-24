<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractRenewalReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $contract;

    public function __construct($contract)
    {
        $this->contract = $contract;
    }

    public function build()
    {
        return $this->subject('Contract Expiry Reminder')
                    ->view('emails.renewal_reminder')
                    ->with([
                        'contract' => $this->contract,
                        'daysLeft' => Carbon::today()->diffInDays($this->contract->end_date),
                    ]);
    }
}
