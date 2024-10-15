<?php

namespace App\Jobs;

use App\Mail\InvoiceGenerated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $invoice;
    protected $pdfPath;

    public function __construct($email, $invoice, $pdfPath)
    {
        $this->email   = $email;
        $this->invoice = $invoice;
        $this->pdfPath = $pdfPath;
    }

    public function handle()
    {
        Log::info('SendInvoiceEmail job started', ['email' => $this->email, 'invoice_id' => $this->invoice->id]);

        try {
            // Send the email with the InvoiceGenerated Mailable
            Mail::to($this->email)->send(new InvoiceGenerated($this->invoice, $this->pdfPath));

            Log::info('Invoice email sent successfully', ['email' => $this->email, 'invoice_id' => $this->invoice->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'email'      => $this->email,
                'invoice_id' => $this->invoice->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
        }
    }
}
