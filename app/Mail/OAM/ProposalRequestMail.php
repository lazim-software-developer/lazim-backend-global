<?php

namespace App\Mail\OAM;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $documentUrl;

    public function __construct($documentUrl)
    {
        $this->documentUrl = $documentUrl;
    }

    public function build()
    {
        return $this->view('emails.proposal_request')
                    ->subject('Request for Proposal')
                    ->attachFromStorageDisk('s3', $this->documentUrl);
    }
}
