<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationHistory extends Model
{
    protected $connection = 'mysql';
    // protected $table = 'notifications_sents';

    protected $fillable = [
        'notification_id',
        'user_id',
        'read_by',
        'read_at',
        'action',
        'owner_association_id'
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
