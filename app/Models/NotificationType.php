<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    use HasFactory;

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'notification_type_id');
    }
}
