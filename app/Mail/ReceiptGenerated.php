<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReceiptGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $receipt;
    public $pdfPath;

    public function __construct($receipt, $pdfPath)
    {
        $this->receipt = $receipt;
        $this->pdfPath = $pdfPath;

        Log::info('ReceiptGenerated mailable instantiated', [
            'receipt_id' => $receipt->id,
            'pdf_path'   => $pdfPath,
        ]);
    }

    public function build()
    {
        Log::info('Building ReceiptGenerated email', [
            'receipt_id' => $this->receipt->id,
        ]);

        if (file_exists($this->pdfPath)) {
            Log::info('PDF found, attaching to email', [
                'pdf_path' => $this->pdfPath,
            ]);

            return $this->view('emails.receipt-generated')
                ->subject('New Receipt Generated')
                ->attach($this->pdfPath, [
                    'as' => 'receipt.pdf',
                    'mime' => 'application/pdf',
                ]);
        } else {
            Log::error('PDF not found, email sent without attachment', [
                'pdf_path' => $this->pdfPath,
            ]);

            return $this->view('emails.receipt-generated')
                ->subject('New Receipt Generated');
        }
    }
}
