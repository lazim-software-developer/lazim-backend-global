<?php

namespace App\Jobs\Forms;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class AccessCardRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $accessCard;
    /**
     * Create a new job instance.
     */
    public function __construct($user, $accessCard)
    {
        $this->user = $user;
        $this->accessCard = $accessCard;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $beautymail = app()->make(Beautymail::class);

        $beautymail->send('emails.forms.access_card_request', [
            'user' => $this->user,
            'ticket_number' => $this->accessCard->ticket_number,
            'building' => $this->accessCard->building->name,
            'flat' => $this->accessCard->flat->plot_number,
            'type' => 'Access Card',
            'card_type' => $this->accessCard->card_type,
        ], function ($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Access card Request Submitted');
        });
    }
}
