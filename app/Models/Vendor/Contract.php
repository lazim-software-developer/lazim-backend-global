<?php

namespace App\Models\Vendor;

use App\Models\Accounting\WDA;
use App\Models\Vendor\Vendor;
use App\Models\Master\Service;
use App\Models\Building\Building;
use App\Models\Accounting\Invoice;
use App\Models\OwnerAssociation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = [
        'start_date',
        'end_date',
        'document_url',
        'contract_type',
        'amount',
        'building_id',
        'service_id',
        'vendor_id',
        'budget_amount',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
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
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function wdas()
    {
        return $this->hasMany(WDA::class);
    }
}
