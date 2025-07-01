<?php

namespace App\Models;

use Sushi\Sushi;
use App\Models\User\User;
use Illuminate\Support\Arr;
use App\Models\MollakTenant;
use App\Models\Building\Flat;
use App\Models\Forms\SaleNOC;
use App\Models\Vendor\Vendor;
use Spatie\Sluggable\HasSlug;
use App\Models\ApartmentOwner;
use App\Models\Community\Poll;
use App\Models\Community\Post;
use App\Models\Vendor\Contract;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use Spatie\Sluggable\SlugOptions;
use App\Models\Building\Complaint;
use App\Services\GenericHttpService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Building\FacilityBooking;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OwnerAssociation extends Model
{
    use SoftDeletes, HasFactory; //, HasSlug;
    // use Sushi;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'trn_number',
        'address',
        'mollak_id',
        'verified',
        'verified_by',
        'active',
        'profile_photo',
        'bank_account_number',
        'trn_certificate',
        'trade_license',
        'dubai_chamber_document',
        'memorandum_of_association',
        'slug',
        'created_by',
        'updated_by',
        'resource',
        'password',
    ];


    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function CreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function saleNoc()
    {
        return $this->hasMany(SaleNOC::class);
    }

    // Define a one-to-many relationship with Budget
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function buildings()
    {
        return $this->belongsToMany(Building::class, 'building_owner_association');
    }

    public function facilityBookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }
    public function owners()
    {
        return $this->hasMany(ApartmentOwner::class);
    }
    public function tenants()
    {
        return $this->hasMany(MollakTenant::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    public function mailCredentials()
    {
        return $this->hasMany(AccountCredentials::class, 'oa_id');
    }
    public function items()
    {
        return $this->hasMany(Item::class, 'owner_association_id');
    }
    public function itemInventories()
    {
        return $this->hasMany(ItemInventory::class, 'owner_association_id');
    }
    public function assets()
    {
        return $this->hasMany(Asset::class, 'owner_association_id');
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'owner_association_id');
    }
    public function oacomplaintReports()
    {
        return $this->hasMany(OacomplaintReports::class, 'owner_association_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'owner_association_vendor')->withPivot(['status']);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
    public function flats()
    {
        return $this->hasManyThrough(
            Flat::class,
            Building::class,
            'owner_association_id', // Foreign key on buildings table
            'building_id',         // Foreign key on flats table
            'id',                 // Local key on owner_associations table
            'id'                 // Local key on buildings table
        );
    }
}
