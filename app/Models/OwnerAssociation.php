<?php

namespace App\Models;

use App\Models\Accounting\Budget;
use App\Models\Forms\SaleNOC;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class OwnerAssociation extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name', 'phone', 'email', 'trn_number',
        'address', 'mollak_id', 'verified', 'verified_by', 'active', 'profile_photo'
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
}
