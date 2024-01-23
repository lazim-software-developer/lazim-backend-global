<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRemarks extends Model
{
    use HasFactory;
    protected $fillable = ['vendor_id','user_id','remarks','status'];
}
