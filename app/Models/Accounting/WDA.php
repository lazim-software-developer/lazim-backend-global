<?php

namespace App\Models\Accounting;

use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\Contract;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WDA extends Model
{
    use HasFactory;

    protected $table = 'wda';

    protected $fillable = [
        'date',
        'job_description',
        'document',
        'created_by',
        'status',
        'remarks',
        'building_id',
        'contract_id',
        'status_updated_by',
        'vendor_id',
        'wda_number',
    ];
    
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'wda_id');
    }
}
