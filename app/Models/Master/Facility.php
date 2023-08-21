<?php

namespace App\Models\Master;

use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Facility extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'icon', 'active'];

    protected $searchableFields = ['*'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }

    public function buildings()
    {
        return $this->belongsToMany(Building::class);
    }
}
