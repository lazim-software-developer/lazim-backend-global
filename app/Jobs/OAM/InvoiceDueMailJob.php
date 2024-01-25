<?php

namespace App\Jobs\OAM;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class InvoiceDueMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $owner;

    public $content;

    public function __construct($owner, $content)
    {
        $this->owner = $owner;
        $this->content = $content;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.payment_due', ['owner' => $this->owner, 'content' => $this->content], function($message) {
            $message
                ->to($this->owner->email, $this->owner->name)
                ->subject('Reminder: Outstanding Balance');
        });
    }
}
