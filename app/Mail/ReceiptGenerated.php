<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReceiptGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $receipt;
    public $pdfPath;
    public $pm_oa;
    public $property_manager_logo;

    public function __construct($receipt, $pdfPath, $pm_oa, $property_manager_logo = null)
    {
        $this->receipt               = $receipt;
        $this->pdfPath               = $pdfPath;
        $this->pm_oa                 = $pm_oa;
        $this->property_manager_logo = $property_manager_logo;
    }

    public function build()
    {
        $mail = $this->view('emails.receipt-generated')
            ->subject('Receipt Confirmation for Your Payment')
            ->with([
                'property_manager_logo' => $this->property_manager_logo,
            ]);

        if (file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as'   => 'receipt.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
