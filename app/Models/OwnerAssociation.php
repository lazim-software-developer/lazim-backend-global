<?php

namespace App\Models;

use Sushi\Sushi;
use App\Models\User\User;
use Illuminate\Support\Arr;
use App\Models\Forms\SaleNOC;
use App\Models\Vendor\Vendor;
use Spatie\Sluggable\HasSlug;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use App\Models\Building\Complaint;
use App\Models\Building\FacilityBooking;
use App\Models\Building\Flat;
use App\Models\Forms\SaleNOC;
use Spatie\Sluggable\HasSlug;
use App\Models\ApartmentOwner;
use App\Models\Community\Poll;
use App\Models\Community\Post;
use App\Models\Vendor\Contract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;
use App\Services\GenericHttpService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;


class OwnerAssociation extends Model
{
    use SoftDeletes, HasFactory; //, HasSlug;
    // use Sushi;

    protected $connection = 'mysql';

    const OA_TYPE = 'globalOa';

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
        'role',
        'emirates_id',
        'trade_license_number',
        'bank_account_holder_name'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
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
    public function owners()
    {
        return $this->hasMany(ApartmentOwner::class);
    }

    public function buildings(){
        return $this->belongsToMany(Building::class, 'building_owner_association')
        ->withPivot(['from', 'to', 'active']);
    }

    public function propertyManagerFlats()
    {
        return $this->belongsToMany(Flat::class, 'property_manager_flats')
            ->withPivot(['active']);
    }

    public function facilityBookings(){
        return $this->hasMany(FacilityBooking::class);
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
    public function flats()
    {
        return $this->hasMany(Flat::class);
    }


    public function emailTemplates(){
        return $this->hasMany(EmailTemplate::class, 'owner_association_id');
    }

    public function bulkEmailManagement(){
        return $this->hasMany(BulkEmailManagement::class, 'owner_association_id');
    }
}
