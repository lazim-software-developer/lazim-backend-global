<?php

namespace App\Models\Building;

use App\Models\Building\Building;
use App\Models\Community\Comment;
use App\Models\Media;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complaint extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'complaintable_type',
        'complaintable_id',
        'user_id',
        'complaint',
        'category',
        'open_time',
        'close_time',
        'photo',
        'remarks',
        'status',
        'owner_association_id',
        'building_id',
        'closed_by',
        'complaint_type',
        'complaint_details'
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
        'photo' => 'array',
        'remarks' => 'array',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function complaintable()
    {

        return $this->morphTo();
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }
    
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }

    public function getOpenTimeDiffAttribute()
    {
        return Carbon::parse($this->attributes['open_time'])->diffForHumans();
    }
}
