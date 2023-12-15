<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppFeedback extends Model
{
    use HasFactory;

    protected $table = 'app_feedback';
    protected $fillable = ['user_id', 'subject', 'comment'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
