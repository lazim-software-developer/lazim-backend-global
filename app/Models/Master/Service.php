<?php

namespace App\Models\Master;


use App\Models\OaUserRegistration;
use App\Models\Vendor\Vendor;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'building_id', 'active', 'subcategory_id'];

    protected $searchableFields = ['*'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function services()
    {
        return $this->belongsToMany(Vendor::class);
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'service_vendor');
    }

    public function bookings()
    {
        return $this->morphMany(FacilityBooking::class, 'bookable');
    }

    public function getFormattedPriceAttribute()
    {
        if ($this->relationLoaded('vendors') && $this->vendors->isNotEmpty()) {
            $price = number_format($this->vendors->first()->price, 2);
            if (substr($price, -3) == '.00') {
                $price = substr($price, 0, -3);
            }
            return "AED " . $price;
        }
        return null;
    }
    
    public function buildings()
    {
        return $this->belongsToMany(Building::class, 'building_service');
    }

    // Service is included in many tenders through TenderService
    public function tenders()
    {
        return $this->belongsToMany(Tender::class, 'tender_services');
    }
}
