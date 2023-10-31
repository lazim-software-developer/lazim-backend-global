<?php

namespace App\Models\Master;

use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Facility extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'icon','building_id', 'active','owner_association_id'];

    protected $searchableFields = ['*'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function buildings()
    {
        return $this->belongsToMany(Building::class,'building_facility','facility_id','building_id');
    }
     public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }
    public function bookings()
    {
        return $this->morphMany(FacilityBooking::class, 'bookable');
    }
}
