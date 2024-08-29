<?php

namespace App\Models\Master;

use App\Models\Accounting\Budgetitem;
use App\Models\Accounting\SubCategory;
use App\Models\Asset;
use App\Models\Vendor\Vendor;
use App\Models\Vendor\Contract;
use App\Models\TechnicianVendor;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\OaUserRegistration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name','type', 'building_id','icon','active', 'subcategory_id','custom','owner_association_id', 'code', 'price', 'payment_link'];

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
        return $this->hasMany(Tender::class);
    }

    public function technicianVendors()
    {
        return $this->belongsToMany(TechnicianVendor::class, 'service_technician_vendor','service_id')->where('active', true);
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class,'contracts');
    }
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
    
    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function budgetitems()
    {
        return $this->hasMany(Budgetitem::class);
    }
}
