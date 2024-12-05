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

        // Log the invoice data and PDF path for debugging
        Log::info('InvoiceGenerated mailable instantiated', [
            'invoice_id' => $invoice->id,
            'pdf_path'   => $pdfPath,
        ]);
    }

    public function build()
    {
        // Log when the email is being built
        Log::info('Building InvoiceGenerated email', [
            'invoice_id' => $this->invoice->id,
        ]);

        // Ensure the PDF path is valid and the file exists before attaching
        if (file_exists($this->pdfPath)) {
            Log::info('PDF found, attaching to email', [
                'pdf_path' => $this->pdfPath,
            ]);

            return $this->view('emails.invoice-generated')
                ->subject('Invoice Notification and Payment Instructions')
                ->attach($this->pdfPath, [
                    'as' => 'invoice.pdf',
                    'mime' => 'application/pdf',
                ]);
        } else {
            Log::error('PDF not found, email sent without attachment', [
                'pdf_path' => $this->pdfPath,
            ]);

            // Optionally, send the email without the attachment
            return $this->view('emails.invoice-generated')
                ->subject('Invoice Notification and Payment Instructions');
        }
    }

}
