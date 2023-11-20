<?php

namespace App\Models;

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
        'building_id'
    ];

    public function asset() {
        return $this->belongsTo(Asset::class);
    }

    public function building() {
        return $this->belongsTo(Building::class);
    }
}
