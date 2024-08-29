<?php

namespace App\Models;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Floor extends Model
{
    use HasFactory;
    protected $fillable = [
        'floors',
        'building_id',
        'qr_code',
    ];
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
