<?php

namespace App\Models\Vendor;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceVendor extends Model
{
    use HasFactory;

    protected $table = 'service_vendor';

    protected $fillable = ['service_id', 'vendor_id', 'price', 'start_date', 'end_date', 'active', 'building_id'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function getFormattedPriceAttribute()
    {
        $price = number_format($this->price, 2);
        if (substr($price, -3) == '.00') {
            $price = substr($price, 0, -3);
        }
        return "AED " . $price;
    }
}
