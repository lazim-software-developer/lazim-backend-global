<?php

namespace App\Models\Vendor;

use App\Models\Building\Building;
use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_date',
        'end_date',
        'document_url',
        'contract_type',
        'building_id',
        'service_id',
        'vendor_id',
    ];
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
