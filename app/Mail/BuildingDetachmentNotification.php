<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BuildingDetachmentNotification extends Mailable
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
        Log::info("Building email notification", [
            'building' => $this->buildingName,
            'type'     => $this->type,
            'due_date' => $this->dueDate,
        ]);

        return $this->subject($this->type === 'due'
            ? 'Building Contract Expiring Tomorrow'
            : 'Building Contract Expired')
            ->view('emails.building-detachment')
            ->with([
                'buildingName' => $this->buildingName,
                'type'         => $this->type,
                'dueDate'      => $this->dueDate,
            ]);
    }
}
