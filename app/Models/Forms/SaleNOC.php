<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Forms\NocContacts;
use App\Models\Order;

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
        'user_id',
        'owner_association_id',
        'signing_authority_email',
        'signing_authority_phone',
        'signing_authority_name',
        'submit_status',
        'remarks',
        'payment_link'
    ];

    public function contacts()
    {
        return $this->hasMany(NocContacts::class, 'noc_form_id');
    }
    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
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
    
    public function orders()
    {
        return $this->morphMany(Order::class, 'orderable');
    }
}
