<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Snowfire\Beautymail\Beautymail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class InvoiceNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailCredentials;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $email,
        public string $name,
        public string $invoice_number,
        public string $issue_date,
        public string $due_date,
        public string $due_amount,
        array $mailCredentials,
        public string $OaName
    ) {
        $this->mailCredentials = $mailCredentials;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Add validation to ensure mailCredentials exists
        if (!is_array($this->mailCredentials) || 
        !isset($this->mailCredentials['mail_from_address'])) {
        throw new \Exception('Mail credentials are missing or invalid');
        }

        Config::set('mail.mailers.smtp.host', $this->mailCredentials['mail_host']);
        Config::set('mail.mailers.smtp.port', $this->mailCredentials['mail_port']);
        Config::set('mail.mailers.smtp.username', $this->mailCredentials['mail_username']);
        Config::set('mail.mailers.smtp.password', $this->mailCredentials['mail_password']);
        Config::set('mail.mailers.smtp.encryption', $this->mailCredentials['mail_encryption']);
        Config::set('mail.from.address', $this->mailCredentials['mail_from_address']);

        try {
            $beautymail = app()->make(Beautymail::class);
            $beautymail->send('emails.InvoiceNotification', 
                [
                    'name' => $this->name,
                    'invoice_number' => $this->invoice_number,
                    'issue_date' => $this->issue_date,
                    'due_date' => $this->due_date,
                    'due_amount' => $this->due_amount,
                    'pm_oa' => $this->OaName
                ], 
                function ($message) {
                    $fromAddress = $this->mailCredentials['mail_from_address'] ?? config('mail.from.address');
                    $fromName = env('MAIL_FROM_NAME', 'Lazim');
                    
                    $message
                        ->from($fromAddress, $fromName)
                        ->to($this->email, $this->name)
                        ->subject($this->OaName.' - Invoice Notification!');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Email sending failed: ' . $e->getMessage());
            throw $e;
        }
    }
}