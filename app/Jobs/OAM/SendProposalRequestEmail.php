<?php

namespace App\Jobs\OAM;

use App\Mail\OAM\ProposalRequestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Snowfire\Beautymail\Beautymail;

class SendProposalRequestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vendors;
    protected $documentUrl;

    public function __construct($vendors, $documentUrl)
    {
        $this->vendors = $vendors;
        $this->documentUrl = $documentUrl;
    }

    public function handle()
    {
        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);

        foreach ($this->vendors as $vendor) {
            $beautymail->send('emails.proposal_request', ['vendor' => $vendor], function ($message) use ($vendor) {
                $message
                    ->to($vendor->user->email, $vendor->name)
                    ->subject('Request for Proposal');

                // Attach the file
                $tempPath = tempnam(sys_get_temp_dir(), 'attachment');
                copy(Storage::disk('s3')->url($this->documentUrl), $tempPath);
                $message->attach($tempPath, [
                    'as' => 'proposal_request.pdf',
                ]);
            });
        }
    }

}
