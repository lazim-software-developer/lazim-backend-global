<?php

namespace App\Models\Gatekeeper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patrolling extends Model
{
    use HasFactory;

    protected $fillable = ['building_id', 'patrolled_by', 'floor_id', 'patrolled_at'];

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
