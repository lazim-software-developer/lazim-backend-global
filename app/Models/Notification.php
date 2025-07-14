<?php

namespace App\Models;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends DatabaseNotification
{
    // use SoftDeletes;

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
        'id' => 'string',
        'data' => 'array',
    ];


    public function ownerAssociation()
    {
        return $this->belongsTo(
            OwnerAssociation::class
        );
    }
    public function histories()
    {
        return $this->hasMany(NotificationHistory::class, 'notification_id');
    }
}
