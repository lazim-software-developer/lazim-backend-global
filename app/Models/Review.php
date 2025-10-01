<?php

namespace App\Models;

use App\Enums\ReviewType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id', 'oa_id', 'flat_id', 'type', 'comment', 'feedback',
    ];

    protected $casts = [
        'type' => ReviewType::class, 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
