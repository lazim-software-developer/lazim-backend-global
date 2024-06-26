<?php

namespace App\Jobs\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class SalesNocRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $dataObj;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $dataObj)
    {
        $this->user = $user;
        $this->dataObj = $dataObj;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $beautymail = app()->make(Beautymail::class);

        $beautymail->send('emails.forms.sale_noc_request', [
            'user' => $this->user,
            'ticket_number' => $this->dataObj->ticket_number,
            'building' => $this->dataObj->building->name,
            'flat' => $this->dataObj->flat->property_number,
            'type' => 'Sale Noc',
        ], function ($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Sale Nocs Request Submitted');
        });
    }
}
