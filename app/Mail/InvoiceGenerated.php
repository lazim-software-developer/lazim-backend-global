<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $pdfPath;
    public $pm_oa;
    public $property_manager_logo;

    public function __construct($invoice, $pdfPath, $pm_oa, $property_manager_logo = null)
    {
        $this->invoice               = $invoice;
        $this->pdfPath               = $pdfPath;
        $this->pm_oa                 = $pm_oa;
        $this->property_manager_logo = $property_manager_logo;
    }

    public function build()
    {
        $mail = $this->view('emails.invoice-generated')
            ->subject('Invoice Notification and Payment Instructions')
            ->with([
                'property_manager_logo' => $this->property_manager_logo,
            ]);

        if (file_exists($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as'   => 'invoice.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }

}
