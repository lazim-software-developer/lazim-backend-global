<?php

namespace App\Jobs;

use App\Mail\ReceiptGenerated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReceiptEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $receipt;
    protected $pdfPath;

    public function __construct($email, $receipt, $pdfPath)
    {
        $this->email   = $email;
        $this->receipt = $receipt;
        $this->pdfPath = $pdfPath;
    }

    public function handle()
    {
        Log::info('SendReceiptEmail job started', ['email' => $this->email, 'receipt_id' => $this->receipt->id]);

        try {
            Mail::to($this->email)->send(new ReceiptGenerated($this->receipt, $this->pdfPath));
            Log::info('Receipt email sent successfully', ['email' => $this->email, 'receipt_id' => $this->receipt->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send receipt email', [
                'email'      => $this->email,
                'receipt_id' => $this->receipt->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
        }
    }
}
