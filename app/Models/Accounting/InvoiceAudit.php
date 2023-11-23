<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceAudit extends Model
{
    use HasFactory;

    protected $table = 'invoice_audit';

    protected $fillable = [
        'building_id',
        'contract_id',
        'invoice_number',
        'wda_id',
        'date',
        'document',
        'created_by',
        'status',
        'remarks',
        'status_updated_by',
        'vendor_id',
        'invoice_amount',
        'invoice_id',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);    
    }

}
