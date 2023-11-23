<?php

namespace App\Models\Building;

use App\Models\Accounting\Budget;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\WDA;
use App\Models\Asset;
use App\Models\Community\Post;
use App\Models\Forms\AccessCard;
use App\Models\Forms\Guest;
use App\Models\Forms\SaleNOC;
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
use App\Models\Forms\FitOutForm;
use App\Models\Forms\MoveInOut;
use App\Models\Vendor\Contact;
use App\Models\Vendor\Contract;
use App\Models\Vendor\PPM;
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
    public function saleNoc()
    {
        return $this->hasMany(SaleNOC::class);
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
    public function moveinOut()
    {
        return $this->hasMany(MoveInOut::class);
    }
    public function mollakTenants()
    {
        return $this->hasMany(MollakTenant::class);
    }
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }
    public function fitOut()
    {
        return $this->hasMany(FitOutForm::class);
    }
    public function accesscards()
    {
        return $this->hasMany(AccessCard::class);
    }

    // OAM accounting 
    // Define a one-to-many relationship with Budget
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'building_service');
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class,'contracts');
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function wdas()
    {
        return $this->hasMany(WDA::class);
    }
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function ppms()
    {
        return $this->hasMany(PPM::class);
    }
}
