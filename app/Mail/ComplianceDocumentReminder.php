<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComplianceDocumentReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(protected $complianceDocument)
    {
        //
    }

    public function build()
    {
        return $this->subject('Compliance Document Expiry Reminder')
                    ->view('emails.compliance_document_reminder')
                    ->with([
                        'complianceDocument' => $this->complianceDocument,
                        'daysLeft' => Carbon::today()->diffInDays($this->complianceDocument->expiry_date),
                    ]);
    }
}
