<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
