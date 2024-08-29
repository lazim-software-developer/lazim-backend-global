<?php

namespace App\Jobs\OAM;

use App\Mail\OAM\ProposalRequestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Snowfire\Beautymail\Beautymail;

class SendProposalRequestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vendors;
    protected $documentUrl;

    public function __construct($vendors, $documentUrl,protected $mailCredentials)
    {
        $this->vendors = $vendors;
        $this->documentUrl = $documentUrl;
    }

    public function handle()
    {
        Config::set('mail.mailers.smtp.host', $this->mailCredentials['mail_host']);
        Config::set('mail.mailers.smtp.port', $this->mailCredentials['mail_port']);
        Config::set('mail.mailers.smtp.username', $this->mailCredentials['mail_username']);
        Config::set('mail.mailers.smtp.password', $this->mailCredentials['mail_password']);
        Config::set('mail.mailers.smtp.encryption', $this->mailCredentials['mail_encryption']);
        Config::set('mail.mailers.smtp.email', $this->mailCredentials['mail_from_address']);
        
        $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
        foreach ($this->vendors as $vendor) {
            $beautymail->send('emails.proposal_request', ['vendor' => $vendor], function ($message) use ($vendor) {
                $message
                    ->from($this->mailCredentials['mail_from_address'],env('MAIL_FROM_NAME'))
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

        Artisan::call('queue:restart');
    }

}
