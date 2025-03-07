<?php

namespace App\Models;

use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceDocument extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'compliance_documents';

    protected $fillable = [
        'doc_name',
        'doc_type',
        'expiry_date',
        'url',
        'vendor_id',
        'last_reminded_at',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

}
