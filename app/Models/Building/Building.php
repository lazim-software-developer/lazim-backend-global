<?php

namespace App\Models\Building;

use App\Models\ApartmentSafety;
use App\Models\BuildingVendor;
use App\Models\EmergencyNumber;
use App\Models\Item;
use App\Models\Asset;
use App\Models\Floor;
use App\Models\Meeting;
use App\Models\OfferPromotion;
use App\Models\User\User;
use App\Models\Vendor\PPM;
use App\Models\Forms\Guest;
use App\Models\Master\City;
use App\Models\Master\Role;
use App\Models\MollakTenant;
use App\Models\Building\Flat;
use App\Models\Forms\SaleNOC;
use App\Models\Vendor\Vendor;
use Spatie\Sluggable\HasSlug;
use App\Models\Accounting\WDA;
use App\Models\Community\Poll;
use App\Models\Community\Post;
use App\Models\CoolingAccount;
use App\Models\Master\Service;
use App\Models\OwnerCommittee;
use App\Models\RuleRegulation;
use App\Models\Vendor\Contact;
use App\Models\BuildingService;
use App\Models\Forms\MoveInOut;
use App\Models\Master\Facility;
use App\Models\Vendor\Contract;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\OwnerAssociation;
use App\Models\Accounting\Budget;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\Attendance;
use Spatie\Sluggable\SlugOptions;
use App\Models\Accounting\Invoice;
use App\Models\Building\Complaint;
use App\Models\Visitor\FlatVisitor;
use App\Models\Building\BuildingPoc;
use App\Models\Accounting\OAMInvoice;
use App\Models\LegalNotice;
use Illuminate\Database\Eloquent\Model;
use App\Models\Visitor\FlatDomesticHelp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Building extends Model
{
    use HasFactory, Searchable;

    protected $connection = 'mysql';

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
        'allow_postupload',
        'slug',
        'cover_photo',
        'show_inhouse_services',
        'mollak_property_id',
        'managed_by',
        'address',
        'building_type',
        'parking_count'
    ];

    protected $casts = [
        'allow_postupload' => 'boolean',
        'show_inhouse_services' => 'boolean'
    ];

    protected $searchableFields = ['*'];

    use HasSlug;

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function cities()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function buildingPocs()
    {
        return $this->hasMany(BuildingPoc::class);
    }
    public function floors()
    {
        return $this->hasMany(Floor::class);
    }
    public function ruleregulations()
    {
        return $this->hasMany(RuleRegulation::class);
    }
    public function appartmentsafety()
    {
        return $this->hasMany(ApartmentSafety::class);
    }
    public function saleNoc()
    {
        return $this->hasMany(SaleNOC::class);
    }
    public function complaint()
    {
        return $this->hasMany(Complaint::class);
    }
    public function incident()
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
        return $this->belongsToMany(Facility::class, 'building_facility', 'building_id', 'facility_id')->withPivot('owner_association_id');
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
        return $this->belongsToMany(OwnerAssociation::class, 'building_owner_association');
    }
    public function SingleownerAssociationData()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function CreatedBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function ownerAssociationData()
    {
        return $this->belongsTo(OwnerAssociation::class,'owner_association_id');
    }

    public function ownerAssociations()
    {
        return $this->belongsToMany(OwnerAssociation::class, 'building_owner_association')
        ->withPivot(['from', 'to', 'active']);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'building_vendor');
    }
    public function buildingvendor()
    {
        return $this->hasMany(BuildingVendor::class);
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
        return $this->hasMany(Contract::class);
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
    public function oaminvoices()
    {
        return $this->hasMany(OAMInvoice::class);
    }

    public function coolingAccounts()
    {
        return $this->hasMany(CoolingAccount::class);
    }

    public function polls()
    {
        return $this->belongsToMany(Poll::class);
    }
    public function ownercommittees()
    {
        return $this->hasMany(OwnerCommittee::class);
    }
    public function buildingservice()
    {
        return $this->hasMany(BuildingService::class);
    }
    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }
    public function emergencyNumbers()
    {
        return $this->hasMany(EmergencyNumber::class);
    }
    public function offerPromotions()
    {
        return $this->hasMany(OfferPromotion::class);
    }

    public function legalNotices()
    {
        return $this->hasMany(LegalNotice::class);
    }

    public function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->lat,
            "lng" => (float)$this->lng,
        ];
    }

    public function setLocationAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes['lat'] = $location['lat'];
            $this->attributes['lng'] = $location['lng'];
            unset($this->attributes['location']);
        }
    }

    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => 'lat',
            'lng' => 'lng',
        ];
    }



}
