<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class FitOutContractorMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $name,protected $email,protected $form)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.contractor-fitout-form', [
            'name' => $this->name,
            'email' => $this->email,
            'id' => $this->form->id,
            'ticket_number' => $this->form->ticket_number,
            'building' => $this->form->building->name,
            'flat' => $this->form->flat->plot_number,
        ], function($message) {
            $message
                ->to($this->email, $this->name)
                ->subject('Fit-out Request Submitted');
        });
    }
}
