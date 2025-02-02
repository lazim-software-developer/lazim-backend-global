<?php
namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class SendSubcontractorCreatedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $subContractor)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $start_date = Carbon::parse($this->subContractor->start_date)->format('d-M-Y');
        $end_date   = Carbon::parse($this->subContractor->end_date)->format('d-M-Y');

        $beautymail = app()->make(Beautymail::class);
        $beautymail->send('emails.subContractorCreated', [
            'subContractor'         => $this->subContractor,
            'start_date'            => $start_date,
            'end_date'              => $end_date,
        ], function ($message) {
            $message
                ->to($this->subContractor->email, $this->subContractor->name)
                ->subject('Successful Account Creation On Lazim Portal');
        });

    }
}
