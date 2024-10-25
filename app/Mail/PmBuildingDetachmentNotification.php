<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PmBuildingDetachmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $buildingName;
    protected $type;
    protected $dueDate;

    public function __construct($buildingName, $type, $dueDate = null)
    {
        $this->buildingName = $buildingName;
        $this->type         = $type;
        $this->dueDate      = $dueDate;
    }

    public function build()
    {
        Log::info("Building email notification for Property Manager", [
            'building' => $this->buildingName,
            'type'     => $this->type,
            'due_date' => $this->dueDate,
        ]);

        return $this->subject($this->type === 'due'
            ? "Property Management Contract Expiring Tomorrow - {$this->buildingName}"
            : "Property Management Contract Expired - {$this->buildingName}")
            ->view('emails.pm-building-detachment')
            ->with([
                'buildingName' => $this->buildingName,
                'type'         => $this->type,
                'dueDate'      => $this->dueDate,
            ]);
    }
}
