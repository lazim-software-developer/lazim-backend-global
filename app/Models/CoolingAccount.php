<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoolingAccount extends Model
{
    use HasFactory;

    protected $table = 'cooling_accounts';

    protected $fillable = [
        'building_id',
        'flat_id',
        'date',
        'opening_balance',
        'consumption',
        'demand_charge',
        'security_deposit',
        'billing_charges',
        'other_charges',
        'receipts',
        'closing_balance',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
