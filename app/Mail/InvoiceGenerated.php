<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InvoiceGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $pdfPath;
    public $pm_oa;

    public function __construct($invoice, $pdfPath, $pm_oa)
    {
        $this->invoice = $invoice;
        $this->pdfPath = $pdfPath;
        $this->pm_oa = $pm_oa;

    }

    public function build()
    {

        // Ensure the PDF path is valid and the file exists before attaching
        if (file_exists($this->pdfPath)) {

            return $this->view('emails.invoice-generated')
                ->subject('Invoice Notification and Payment Instructions')
                ->attach($this->pdfPath, [
                    'as' => 'invoice.pdf',
                    'mime' => 'application/pdf',
                ]);
        } else {

            // Optionally, send the email without the attachment
            return $this->view('emails.invoice-generated')
                ->subject('Invoice Notification and Payment Instructions');
        }
    }

}
