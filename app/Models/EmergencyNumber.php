<?php

namespace App\Models;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyNumber extends Model
{
    use HasFactory;
    protected $table = 'emergency_numbers';

     protected $fillable = [
        'name',
        'number',
        'building_id',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
