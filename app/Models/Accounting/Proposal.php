<?php

namespace App\Models\Accounting;

use App\Models\User\User;
use App\Models\Accounting\Tender;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory;
    protected $fillable = [
        'tender_id',
        'amount',
        'submitted_by',
        'submitted_on',
        'document',
        'status',
        'remarks',
        'status_updated_by',
        'status_updated_on',
        'vendor_id'
    ];
    public function tender()
    {
        return $this->belongsTo(Tender::class);
    }
    public function submittedBy()
    {
        return $this->belongsTo(Vendor::class,'submitted_by');
    }
    public function stausUpdatedBy()
    {
        return $this->belongsTo(User::class,'status_updated_by');
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
