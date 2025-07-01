<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends DatabaseNotification
{
    protected $fillable = [
        'read_at',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(
            OwnerAssociation::class
        );
    }
}
