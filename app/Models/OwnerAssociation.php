<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\Forms\SaleNOC;
use Spatie\Sluggable\HasSlug;
use App\Models\Community\Poll;
use App\Models\Community\Post;
use App\Models\Vendor\Contract;
use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use Spatie\Sluggable\SlugOptions;
use App\Models\Building\Complaint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Building\FacilityBooking;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OwnerAssociation extends Model
{
    use SoftDeletes,HasFactory;//, HasSlug;

    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'phone', 'email', 'trn_number',
        'address', 'mollak_id', 'verified', 'verified_by', 'active', 'profile_photo','bank_account_number','trn_certificate',
        'trade_license','dubai_chamber_document','memorandum_of_association','slug','created_by','updated_by','resource'
    ];

    /**
     * Get the options for generating the slug.
     */
    // public function getSlugOptions(): SlugOptions
    // {
    //     return SlugOptions::create()
    //         ->generateSlugsFrom('name')
    //         ->saveSlugsTo('slug');
    // }

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

    public function building(){
        return $this->belongsToMany(Building::class, 'building_owner_association');
    }

    public function facilityBookings(){
        return $this->hasMany(FacilityBooking::class);
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }
    public function polls(){
        return $this->hasMany(Poll::class);
    }

    public function mailCredentials()
    {
        return $this->hasMany(AccountCredentials::class,'oa_id');
    }
    public function items()
    {
        return $this->hasMany(Item::class,'owner_association_id');
    }
    public function itemInventories()
    {
        return $this->hasMany(ItemInventory::class,'owner_association_id');
    }
    public function assets()
    {
        return $this->hasMany(Asset::class,'owner_association_id');
    }
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class,'owner_association_id');
    }
    public function oacomplaintReports()
    {
        return $this->hasMany(OacomplaintReports::class,'owner_association_id');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
    public function getProfilePhotoAttribute($value)
    {
        return !empty($value) ? Storage::disk('s3')->url($value) : null;
    }
    public function getTradeLicenseAttribute($value)
    {
        return !empty($value) ? Storage::disk('s3')->url($value) : null;
    }
    public function getDubaiChamberDocumentAttribute($value)
    {
        return !empty($value) ? Storage::disk('s3')->url($value) : null;
    }
    public function getTrnCertificateAttribute($value)
    {
        return !empty($value) ? Storage::disk('s3')->url($value) : null;
    }
    public function getMemorandumOfAssociationAttribute($value)
    {
        return !empty($value) ? Storage::disk('s3')->url($value) : null;
    }
}
