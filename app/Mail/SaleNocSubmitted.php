<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SaleNocSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public $saleNoc;
    public $documentPath;

    public function __construct($saleNoc, $documentPath)
    {
        $this->saleNoc = $saleNoc;
        $this->documentPath = $documentPath;
    }

    public function build()
    {
        return $this->view('emails.salenoc_submitted', ['saleNoc' => $this->saleNoc])
            ->with([
                'saleNoc' => $this->saleNoc,
            ])
            ->attachFromStorageDisk('s3', $this->documentPath->document);
    }
}
