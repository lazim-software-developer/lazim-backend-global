<?php

namespace App\Models\Building;

use App\Models\Vendor\Attendance;
use App\Models\Building\Flat;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Models\Building\Document;
use App\Models\Master\City;
use App\Models\Master\Facility;
use App\Models\Scopes\Searchable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'unit_number',
        'address_line1',
        'address_line2',
        'area',
        'city_id',
        'lat',
        'lng',
        'description',
        'floors',
    ];

    protected $searchableFields = ['*'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function pocs()
    {
        return $this->hasMany(BuildingPoc::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function flats()
    {
        return $this->hasMany(Flat::class);
    }

    public function facilities()
    {
        return $this->belongsToMany(Facility::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complaintable');
    }
}
