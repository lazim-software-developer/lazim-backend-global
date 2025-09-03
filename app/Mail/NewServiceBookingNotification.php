<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewServiceBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $facilityBooking;
    /**
     * Create a new message instance.
     */
    public function __construct($facilityBooking)
    {
        $this->facilityBooking = $facilityBooking;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Service Booking Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('Sending New Service Booking Notification', [
            'facilityBooking' => $this->facilityBooking,
        ]);
        return new Content(
            view: 'emails.newServiceBooking',
            with: [
                'facilityBooking' => $this->facilityBooking,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
