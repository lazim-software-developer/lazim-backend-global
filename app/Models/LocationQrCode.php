<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LocationQrCode extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    
    protected $fillable = [
        'floor_name',
        'building_id',
        'floor_id',
        'qr_code',
    ];
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }
}
