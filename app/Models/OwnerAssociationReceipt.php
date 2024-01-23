<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerAssociationReceipt extends Model
{
    use HasFactory;

    protected $table = 'owner_association_receipts';

    protected $fillable = [
        'date', 'type', 'receipt_number','paid_by','payment_method','received_in','payment_reference',
        'on_account_of','receipt_document','flat_id','owner_association_id','building_id','receipt_to','amount'
    ];

    public function building(){
        return $this->belongsTo(Building::class);
    }

    public function flat(){
        return $this->belongsTo(Flat::class);
    }

    public function owner(){
        return $this->belongsTo(OwnerAssociation::class,'owner_association_id');
    }
}
