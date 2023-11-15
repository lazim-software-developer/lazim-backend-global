<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianVendor extends Model
{
    use HasFactory;

    protected $fillable =['technician_id', 'vendor_id', 'active', 'position'];
}
