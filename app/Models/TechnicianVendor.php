<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianVendor extends Model
{
    use HasFactory;
    protected $table = "technician_vendors";
    protected $fillable =['technician_id', 'vendor_id', 'active', 'position'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
