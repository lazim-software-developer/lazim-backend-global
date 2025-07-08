<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends DatabaseNotification
{

    protected $connection = 'mysql';
    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'notifiable_id',
        'notifiable_type',
        'data',
        'read_at',
        'owner_association_id',
        'notification_type_id'
    ];
    protected $casts = [
        // 'id' => 'string',
        // 'data' => 'array',
    ];

    // public function notificationType()
    // {
    //     return $this->belongsTo(NotificationType::class, 'notification_type_id');
    // }

    public function ownerAssociation()
    {
        return $this->belongsTo(
            OwnerAssociation::class
        );
    }
}
