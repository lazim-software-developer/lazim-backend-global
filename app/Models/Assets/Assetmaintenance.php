<?php

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assetmaintenance extends Model
{
    use HasFactory;

    protected $table = 'asset_maintenance';

    protected $fillable = [
        'asset_id',
        'maintenance_date',
        'comment',
        'media',
        'maintained_by',
        'building_id',
        'status',
        'technician_asset_id'
    ];

    /**
     * Get the asset associated with the maintenance.
     */
    public function asset()
    {
        return $this->belongsTo(AssetMaintenance::class, 'technician_asset_id');
    }

    /**
     * Get the user who maintained the asset.
     */
    public function maintainer()
    {
        return $this->belongsTo(User::class, 'maintained_by');
    }

    /**
     * Get the building associated with the maintenance.
     */
    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
