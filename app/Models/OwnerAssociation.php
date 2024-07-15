<?php

namespace App\Models;

use App\Models\Accounting\Budget;
use App\Models\Building\Building;
use App\Models\Building\FacilityBooking;
use App\Models\Community\Post;
use App\Models\Forms\SaleNOC;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class OwnerAssociation extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name', 'phone', 'email', 'trn_number',
        'address', 'mollak_id', 'verified', 'verified_by', 'active', 'profile_photo','bank_account_number','trn_certificate',
        'trade_license','dubai_chamber_document','memorandum_of_association'
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

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
}
