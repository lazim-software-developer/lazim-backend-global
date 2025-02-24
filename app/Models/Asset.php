<?php

namespace App\Models;

use App\Models\Master\Service;
use App\Models\Building\Building;
use App\Models\User\User;
use App\Models\Vendor\PPM;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    
    protected $fillable = [
        'building_id',
        'name',
        'location',
        'description',
        'service_id',
        'qr_code',
        'asset_code',
        'floor',
        'division',
        'discipline',
        'frequency_of_service',
        'owner_association_id',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
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
        return $this->belongsToMany(User::class,'technician_assets','asset_id','technician_id');
    }

    public function ppms()
    {
        return $this->hasMany(PPM::class);
    }
}

