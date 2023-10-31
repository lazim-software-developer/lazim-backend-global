<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NocForms extends Model
{
    use HasFactory;
    protected $fillable = [
        'unit_occupied_by',
        'applicant',
        'unit_area',
        'sale_price',
        'cooling_bill_paid',
        'service_charge_paid',
        'noc_fee_paid',
        'service_charge_paid_till',
        'cooling_receipt_url',
        'cooling_soa_url',
        'cooling_clearance_url',
        'payment_receipt_url',
        'status',
        'verified',
        'building_id',
        'verified_by',
        'flat_id',
    ];

    protected $searchableFields = ['*'];
    protected $casts = [
        'allow_postupload'         => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
