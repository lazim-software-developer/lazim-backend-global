<?php

namespace App\Models\Building;

use App\Models\Master\DocumentLibrary;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\User\User;
use App\Models\Master\City;
use App\Models\Building\Flat;
use App\Models\Master\Facility;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\Attendance;
use App\Models\Building\Complaint;

use App\Models\Building\BuildingPoc;
use App\Models\Vendor\Contact;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatDomesticHelp;
use App\Models\Visitor\FlatVisitor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function cities()
    {
        return $this->belongsTo(City::class);
    }

    public function buildingPocs()
    {
        return $this->hasMany(BuildingPoc::class);
    }

    public function complaint()
    {
        return $this->hasMany(Complaint::class);
    }

    public function document()
    {
        return $this->hasMany(Document::class);
    }

    public function facilityBookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }

    public function flatTenants()
    {
        return $this->hasMany(FlatTenant::class);
    }

    public function documentLibraries()
    {
        return $this->hasMany(DocumentLibrary::class);
    }

    public function facility()
    {
        return $this->hasMany(Facility::class);
    }
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
    // public function services()
    // {
    //     return $this->hasMany(Service::class);
    // }
    public function flatVisitors()
    {
        return $this->hasMany(FlatVisitor::class);
    }
    public function contact()
    {
        return $this->hasMany(Contact::class);
    }
    public function flatDomesticHelps()
    {
        return $this->hasMany(FlatDomesticHelp::class);
    }
    // public function vendor()
    // {
    //     return $this->hasMany(Vendor::class);
    // }



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

    public function members()
    {
        return $this->belongsToMany(User::class);
    }
    public function users():BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
