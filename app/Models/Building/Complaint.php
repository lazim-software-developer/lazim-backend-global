<?php

namespace App\Models\Building;

use App\Models\Building\Building;
use App\Models\Community\Comment;
use App\Models\Master\Service;
use App\Models\Media;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Building\Flat;
use App\Models\OwnerAssociation;
use App\Models\Vendor\Vendor;

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
        'complaint_details',
        'service_id',
        'due_date',
        'priority',
        'vendor_id',
        'technician_id',
        'flat_id',
        'complaint_location',
        'ticket_number',
        'type'
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
        'photo' => 'array',
        'remarks' => 'array',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getDueDateDiffAttribute()
    {
        return Carbon::parse($this->attributes['due_date'])->diffForHumans();
    }
    public function technician()
    {
        return $this->belongsTo(User::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
