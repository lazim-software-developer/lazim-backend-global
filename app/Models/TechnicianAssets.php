<?php

namespace App\Models;

use App\Models\Assets\Assetmaintenance;
use App\Models\Asset;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TechnicianAssets extends Model
{
    use HasFactory;

    protected $table = 'technician_assets';

    protected $fillable = [
        'technician_id',
        'vendor_id',
        'asset_id',
        'active',
        'building_id',
        'owner_association_id'
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }

    public function asset() {
        return $this->belongsTo(Asset::class);
    }
    public function user() {
        return $this->belongsTo(User::class,'technician_id');
    }
    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function assetMaintenances() {
        return $this->hasMany(Assetmaintenance::class, 'technician_asset_id');
    }
    public function vendor() {
        return $this->belongsTo(Vendor::class);
    }
}
