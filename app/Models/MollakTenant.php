<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MollakTenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'contract_number', 'emirates_id', 'license_number', 'mobile', 'email', 'start_date',
        'end_date', 'contract_status', 'building_id', 'flat_id'
    ];
}
