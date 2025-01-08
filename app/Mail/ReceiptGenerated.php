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
    public $pm_oa;

    public function __construct($receipt, $pdfPath, $pm_oa)
    {
        $this->receipt = $receipt;
        $this->pdfPath = $pdfPath;
        $this->pm_oa = $pm_oa;

    }

    public function build()
    {

        if (file_exists($this->pdfPath)) {

            return $this->view('emails.receipt-generated')
                ->subject('Receipt Confirmation for Your Payment')
                ->attach($this->pdfPath, [
                    'as' => 'receipt.pdf',
                    'mime' => 'application/pdf',
                ]);
        } else {

            return $this->view('emails.receipt-generated')
                ->subject('Receipt Confirmation for Your Payment');
        }
    }
}
