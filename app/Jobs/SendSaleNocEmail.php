<?php

namespace App\Jobs;

use App\Mail\SaleNocSubmitted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendSaleNocEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $saleNoc;
    protected $documentPath;

    public function __construct($saleNoc, $documentPath)
    {
        $this->saleNoc = $saleNoc;
        $this->documentPath = $documentPath;
    }

    public function handle()
    {
        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);

        $beautymail->send('emails.salenoc_submitted', ['name' => $this->saleNoc->signing_authority_name], function ($message) {
            $message
                ->to($this->saleNoc->signing_authority_email)
                ->subject('Sale NOC');

            // Attach the file
            $tempPath = tempnam(sys_get_temp_dir(), 'attachment');
            $parts = explode('.', $this->documentPath->document);
            $extension = end($parts);
            
            copy(Storage::disk('s3')->url($this->documentPath->document), $tempPath);
            $message->attach($tempPath, [
                'as' => 'sales_noc.'.$extension,
            ]);
        });
    }
}
