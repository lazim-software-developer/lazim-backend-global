<?php

namespace App\Models;

use App\Models\Master\Service;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildingService extends Model
{
    use HasFactory;
    protected $table = 'building_service';
    protected $fillable = ['building_id', 'service_id','active'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
