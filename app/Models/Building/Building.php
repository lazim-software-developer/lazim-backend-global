<?php

namespace App\Models\Building;

use App\Models\Community\Post;
use App\Models\Master\Role;
use App\Models\Master\Service;
use App\Models\MollakTenant;
use App\Models\OwnerAssociation;
use App\Models\Master\City;
use App\Models\Building\Flat;
use App\Models\Master\Facility;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\Attendance;
use App\Models\Building\Complaint;
use App\Models\Building\BuildingPoc;
use App\Models\Forms\Form;
use App\Models\Vendor\Contact;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatDomesticHelp;
use App\Models\Visitor\FlatVisitor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Building extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'name',
        'property_group_id',
        'address_line1',
        'address_line2',
        'area',
        'city_id',
        'lat',
        'lng',
        'description',
        'floors',
        'owner_association_id',
        'allow_postupload'
    ];

    protected $searchableFields = ['*'];
    protected $casts = [
        'allow_postupload'         => 'boolean',
    ];
    public function cities()
    {
        return $this->belongsTo(City::class, 'city_id');
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
    public function services()
    {
        return $this->belongsToMany(Service::class, 'building_services', 'building_id', 'service_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'building_roles', 'building_id', 'role_id');
    }
    public function documentlibraries()
    {
        return $this->belongsToMany(Role::class, 'building_documentlibraries', 'building_id', 'documentlibrary_id');
    }
    public function facilities()
    {
        return $this->belongsToMany(Facility::class, 'building_facility', 'building_id', 'facility_id');
    }
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
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complaintable');
    }
    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'building_vendor');
    }
    public function forms()
    {
        return $this->hasMany(Form::class);
    }
    public function mollakTenants() 
    {
        return $this->hasMany(MollakTenant::class);
    }
}
