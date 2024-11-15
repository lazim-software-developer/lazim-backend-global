<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OverdueInvoiceNotification extends Notification
{
    use Queueable;

    public $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        \Log::info('Sending mail to:', ['email' => $notifiable->email]);

        $pdfPath = storage_path('app/public/invoices/' . $this->invoice->invoice_number . '.pdf');
        $mailMessage = (new MailMessage)
            ->subject('Invoice Overdue Notice')
            ->greeting('Hello ' . $notifiable->first_name)
            ->line('Invoice #' . $this->invoice->invoice_number . ' is overdue.')
            ->line('Due Date: ' . $this->invoice->due_date)
            ->line('Amount: ' . $this->invoice->rate)
            ->line('Please clear this payment as soon as possible.')
            ->error();

        if (file_exists($pdfPath)) {
            \Log::info('PDF file found, attaching to email', ['path' => $pdfPath]);
            $mailMessage->attach($pdfPath, [
                'as' => 'invoice-' . $this->invoice->invoice_number . '.pdf',
                'mime' => 'application/pdf',
            ]);
        } else {
            \Log::warning('PDF file not found', [
                'invoice' => $this->invoice->invoice_number,
                'expected_path' => $pdfPath
            ]);
            $mailMessage->line('Invoice PDF is not available at the moment.');
        }

        return $mailMessage;
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Invoice Overdue',
            'message' => 'Invoice #' . $this->invoice->invoice_number . ' is overdue.',
            'invoice_id' => $this->invoice->id,
        ];
    }
}
