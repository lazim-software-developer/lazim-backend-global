<?php

namespace App\Jobs;

use App\Models\Building\FlatTenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Snowfire\Beautymail\Beautymail;

class SendInactiveStatusToResident implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $flatTenant;

    public function __construct(FlatTenant $flatTenant)
    {
        $this->flatTenant = $flatTenant;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = $this->flatTenant->user;

            if (!$user) {
                return;
            }

            if (!$user->email) {
                return;
            }

            $userData = [
                'name'  => $user->first_name ?? 'Resident',
                'email' => $user->email,
            ];


            $beautymail = app()->make(Beautymail::class);
            $beautymail->send('emails.resident_inactive_status',
                ['user' => $user],
                function ($message) use ($userData) {
                    $message
                        ->to($userData['email'], $userData['name'])
                        ->subject('Account Deactivated!');
                });

        } catch (\Exception $e) {
            Log::error('Failed to send inactive status email:', [
                'error'          => $e->getMessage(),
                'flat_tenant_id' => $this->flatTenant->id,
            ]);
        }
    }
}
