<?php

namespace App\Jobs;

use App\Mail\OaUserRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
// use App\Mail\OaUserRegistration;
use Snowfire\Beautymail\Beautymail;

class AccountCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user;
    public $password;
    public $slug;

    public function __construct($user, $password,$slug)
    {
        $this->user     = $user;
        $this->password = $password;
        $this->slug     = $slug;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            // Check if user exists
            if (!$this->user) {
                \Log::error('User object is null in email handler');
                return false;
            }

            // Check if required user properties exist
            if (!$this->user->email || !$this->user->first_name) {
                \Log::error('Required user properties missing', [
                    'email' => $this->user->email ?? 'missing',
                    'first_name' => $this->user->first_name ?? 'missing'
                ]);
                return false;
            }

            // Check if password and slug exist
            if (!$this->password || !$this->slug) {
                \Log::error('Required parameters missing', [
                    'password' => $this->password ? 'exists' : 'missing',
                    'slug' => $this->slug ? 'exists' : 'missing'
                ]);
                return false;
            }

            $beautymail = app()->make(Beautymail::class);
            
            $data = [
                'user' => $this->user,
                'password' => $this->password,
                'slug' => $this->slug
            ];

            $beautymail->send('emails.oa-user_registration', $data, function($message) {
                $message
                    ->to($this->user->email, $this->user->first_name)
                    ->subject('Welcome to Lazim!');
            });

            return true;

        } catch (\Exception $e) {
            \Log::error('Failed to send welcome email', [
                'error' => $e->getMessage(),
                'user_id' => $this->user->id ?? null
            ]);
            
            return false;
        }
    }
}
