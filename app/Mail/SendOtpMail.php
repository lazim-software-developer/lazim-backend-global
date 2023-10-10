<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Snowfire\Beautymail\Beautymail;

class SendOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $otp;
    public $user;

    public function __construct($otp, $user)
    {
        $this->otp = $otp;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset',
        );
    }

    public function build()
    {
        $beautymail = app()->make(Beautymail::class);
        return $beautymail->send('emails.sendOtp', ['otp' => $this->otp, 'user' => $this->user], function ($message) {
            $message
                ->to($this->user->email, $this->user->first_name)
                ->subject('Your OTP');
        });
    }
}
