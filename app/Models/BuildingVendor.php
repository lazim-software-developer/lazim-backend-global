<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BuildingVendor extends Pivot
{
    public $timestamps = false;
    protected $table = 'building_vendor';
    protected $fillable = ['building_id', 'contract_id', 'vendor_id', 'start_date', 'end_date', 'active'];
}
