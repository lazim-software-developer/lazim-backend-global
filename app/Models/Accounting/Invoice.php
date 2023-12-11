<?php

namespace App\Models\Accounting;

use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\Contract;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

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
        'opening_balance',
        'payment',
        'balance',
        'invoice_amount',
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
    public function wda()
    {
        return $this->belongsTo(WDA::class, 'wda_id');
    }
    public function audits()
    {
        return $this->hasMany(InvoiceAudit::class, 'wda_id');
    }
}
