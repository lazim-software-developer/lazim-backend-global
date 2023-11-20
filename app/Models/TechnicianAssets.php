<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianAssets extends Model
{
    use HasFactory;
    protected $table = 'technician_assets';
    protected $fillable = [
        'technician_id',
        'vendor_id',
        'asset_id',
        'active',
    ];

}
