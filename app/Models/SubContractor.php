<?php

namespace App\Models;

use App\Models\Master\Service;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubContractor extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table      = 'sub_contractors';

    protected $fillable = ['name', 'email', 'phone', 'company_name', 'trn_no', 'service_provided', 'start_date', 'end_date', 'trade_licence', 'contract_paper', 'agreement_letter', 'additional_doc', 'vendor_id', 'active', 'last_reminded_at', 'trade_licence_expiry_date'];

    protected $casts = [
        'active'     => 'boolean',
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
}
