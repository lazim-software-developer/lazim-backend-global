<?php

namespace App\Models;

use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DelinquentOwner extends Model
{
    use HasFactory;
    protected $table = 'delinquent_owners';

    protected $fillable = [
        'building_id',
        'flat_id',
        'owner_id',
        'last_payment_date',
        'last_payment_amount',
        'outstanding_balance',
        'quarter_1_balance',
        'quarter_2_balance',
        'quarter_3_balance',
        'quarter_4_balance',
        'invoice_pdf_link',
        'year'
    ];

    public function flat(){
        return $this->belongsTo(Flat::class);
    }

    public function owner(){
        return $this->belongsTo(ApartmentOwner::class);
    }
}
