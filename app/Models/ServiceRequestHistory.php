<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestHistory extends Model
{

    protected $connection = 'mysql';

    protected $fillable = [
        'record_id',
        'type',
        'action',
        'user_id',
        'action_at',
        'request_json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
