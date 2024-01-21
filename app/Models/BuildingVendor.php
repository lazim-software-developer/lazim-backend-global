<?php

namespace App\Models;

use App\Models\Vendor\Vendor;
use App\Models\Vendor\Contract;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BuildingVendor extends Pivot
{
    public $timestamps = false;
    protected $table = 'building_vendor';
    protected $fillable = ['building_id', 'contract_id', 'vendor_id', 'start_date', 'end_date', 'active'];
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
