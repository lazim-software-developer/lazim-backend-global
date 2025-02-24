<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'asset_maintenance';

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function building() {
        return $this->belongsTo(Building::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'maintained_by');
    }

    public function technicianAsset(){
        return $this->belongsTo(TechnicianAssets::class, 'technician_asset_id');
    }
}
