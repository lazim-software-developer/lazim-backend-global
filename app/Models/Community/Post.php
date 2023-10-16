<?php

namespace App\Models\Community;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content', 'status', 'scheduled_at', 'building_id', 'is_announcement'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }
}
