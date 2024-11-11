<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OverdueReceiptNotification extends Notification
{
    use Queueable;

    public $receipt;

    public function __construct($receipt)
    {
        $this->receipt = $receipt;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        \Log::info('Sending receipt mail to:', ['email' => $notifiable->email]);

        $pdfPath = storage_path('app/public/receipts/' . $this->receipt->receipt_number . '.pdf');
        $mailMessage = (new MailMessage)
            ->subject('Receipt Overdue Notice')
            ->greeting('Hello ' . $notifiable->first_name)
            ->line('Receipt #' . $this->receipt->receipt_number . ' is overdue.')
            ->line('Date: ' . $this->receipt->date)
            ->line('Amount: ' . $this->receipt->amount)
            ->line('Please process this payment as soon as possible.')
            ->error();

        if (file_exists($pdfPath)) {
            $mailMessage->attach($pdfPath, [
                'as' => 'receipt-' . $this->receipt->receipt_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mailMessage;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Receipt Overdue',
            'message' => 'Receipt #' . $this->receipt->receipt_number . ' is overdue.',
            'receipt_id' => $this->receipt->id,
        ];
    }
}
