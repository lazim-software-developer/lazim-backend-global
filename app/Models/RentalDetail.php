<?php

namespace App\Models;

use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_id',
        'number_of_cheques',
        'contract_start_date',
        'contract_end_date',
        'admin_fee',
        'other_charges',
        'advance_amount',
        'advance_amount_payment_mode',
        'status',
        'created_by',
        'status_updated_by',
        'property_manager_id',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusUpdatedBy()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function propertyManager()
    {
        return $this->belongsTo(User::class, 'property_manager_id');
    }
}
