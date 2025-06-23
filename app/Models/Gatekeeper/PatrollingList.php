<?php

namespace App\Models\Gatekeeper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Building\Building;
use App\Models\Floor;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatrollingList extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'patrollings';
    protected $connection = 'mysql';

    protected $fillable = ['patrolling_record_id', 'building_id', 'patrolled_by', 'floor_id', 'location_id', 'location_name', 'patrolled_at','owner_association_id','is_completed'];

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
}
