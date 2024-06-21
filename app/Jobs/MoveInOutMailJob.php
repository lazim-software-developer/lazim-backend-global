<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class MoveInOutMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $user, protected $moveInOut)
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $beautymail = app()->make(Beautymail::class);

        $beautymail->send('emails.send_moveinout-blade', [
            'user' => $this->user,
            'ticket_number' => $this->moveInOut->ticket_number,
            'building_id' => $this->moveInOut->building_id,
            'flat_id' => $this->moveInOut->flat_id,
            'type' => $this->moveInOut->type,
            'moving_date' => $this->moveInOut->moving_date,
            'moving_time' => $this->moveInOut->moving_time,
            'time_preference' => $this->moveInOut->time_preference,
        ], function ($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Move-in/Move-out Request Submitted');
        });
    }
}
