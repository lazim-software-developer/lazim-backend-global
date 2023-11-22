<?php

namespace App\Models;

use App\Models\Building\Complaint;
use App\Models\Master\Service;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianVendor extends Model
{
    use HasFactory;
    protected $table = "technician_vendors";
    protected $fillable =['technician_id', 'vendor_id', 'active', 'position','technician_number'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_technician_vendor', 'technician_vendor_id');
    }
     public function complaint()
     {
        return $this->hasMany(Complaint::class,'technician_id');
     }
}
