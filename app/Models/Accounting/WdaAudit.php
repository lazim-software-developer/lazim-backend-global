<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WdaAudit extends Model
{
    use HasFactory;

    protected $table = 'wda_audit';

    protected $fillable = [
        'date',
        'job_description',
        'document',
        'created_by',
        'status',
        'remarks',
        'building_id',
        'contract_id',
        'status_updated_by',
        'vendor_id',
        'wda_number',
        'wda_id',
    ];

    public function wda()
    {
        return $this->belongsTo(WDA::class);    
    }
}
