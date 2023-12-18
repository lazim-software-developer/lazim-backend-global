<?php

namespace App\Models\Gatekeeper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Building\Building;
use App\Models\Floor;
use App\Models\User\User;
use Carbon\Carbon;

class Patrolling extends Model
{
    use HasFactory;

    protected $fillable = ['building_id', 'patrolled_by', 'floor_id', 'patrolled_at'];

    
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
