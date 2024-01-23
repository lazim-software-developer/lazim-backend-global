<?php

namespace App\Models;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerAssociationInvoice extends Model
{
    use HasFactory;

    protected $table = 'owner_association_invoices';

    protected $fillable = [
        'date', 'due_date', 'type','bill_to','mode_of_payment','supplier_name','job','month','description', 'quantity',
        'rate', 'tax', 'address', 'trn', 'invoice_number','owner_association_id','building_id'
    ];

    public function building(){
        return $this->belongsTo(Building::class);
    }

    public function owner(){
        return $this->belongsTo(OwnerAssociation::class,'owner_association_id');
    }
}
