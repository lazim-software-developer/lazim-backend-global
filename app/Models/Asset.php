<?php

namespace App\Models;

use App\Models\Master\Service;
use App\Models\Building\Building;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;
    protected $fillable = [
        'building_id',
        'name',
        'location',
        'description',
        'service_id',
        'qr_code',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class,'technician_assets','technician_id');
    }
}

