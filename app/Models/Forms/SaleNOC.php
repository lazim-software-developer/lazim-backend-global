<?php

namespace App\Models\Forms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Forms\NocContacts;

class SaleNOC extends Model
{
    use HasFactory;

    protected $table = 'sale_nocs';

    protected $fillable = [
        'unit_occupied_by',
        'applicant',
        'unit_area',
        'sale_price',
        'cooling_bill_paid',
        'service_charge_paid',
        'noc_fee_paid',
        'service_charge_paid_till',
        'cooling_receipt',
        'cooling_soa',
        'cooling_clearance',
        'payment_receipt',
        'status',
        'verified',
        'building_id',
        'verified_by',
        'flat_id',
        'user_id'
    ];

    public function contacts() {
        return $this->hasMany(NocContacts::class);
    }
}
