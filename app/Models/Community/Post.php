<?php

namespace App\Models\Community;

use App\Models\Building\Building;
use App\Models\Media;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Community\PostLike;
use App\Models\User\User;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content', 'status', 'scheduled_at', 'building_id', 'is_announcement'
    ];
    protected $casts = [
        'is_announcement' => 'boolean',
    ];
    protected $appends = ['is_liked_by_user'];

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

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getIsLikedByUserAttribute()
    {
        if (auth()->check()) {
            return $this->likes->where('user_id', auth()->user()->id)->isNotEmpty();
        }
        return false;
    }

    public function getScheduledAtDiffAttribute()
    {
        return Carbon::parse($this->attributes['scheduled_at'])->diffForHumans();
    }
}
