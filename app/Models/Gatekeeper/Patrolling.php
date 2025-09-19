<?php

namespace App\Models\Gatekeeper;

use Carbon\Carbon;
use App\Models\Floor;
use App\Models\User\User;
use App\Models\LocationQrCode;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patrolling extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'patrolling_records';
    protected $connection = 'mysql';

    // protected $fillable = ['building_id', 'patrolled_by', 'floor_id', 'patrolled_at','owner_association_id'];
    protected $fillable = [
        'building_id',
        'is_completed',
        'patrolled_by',
        'owner_association_id',
        'total_count',
        'completed_count',
        'pending_count',
        'started_at',
        'ended_at',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function getPatrolledAtDiffAttribute()
    {
        return Carbon::parse($this->attributes['patrolled_at'])->diffForHumans();
    }

    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function user() {
        return $this->belongsTo(User::class, 'patrolled_by');
    }

    public function floor() {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function patrollingList() {
        return $this->hasMany(PatrollingList::class, 'patrolling_record_id', 'id');
    }

    public function location() {
        return $this->hasMany(LocationQrCode::class, 'location_id', 'id');
    }
}
