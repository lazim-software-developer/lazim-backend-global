<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WDA extends Model
{
    use HasFactory;

    protected $table = 'wda';

    protected $fillable =  ['job_description', 'date', 'document', 'created_by', 'status', 'remarks', 'building_id', 'contract_id', 'status_updated_by', 'vendor_id'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'wda_id');
    }
}
