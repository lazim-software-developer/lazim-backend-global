<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Database\Eloquent\Model;

class OwnerAssociation extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = ['name', 'phone', 'email', 'trn_number', 
        'address', 'mollak_id', 'verified', 'verified_by'
    ];

    public function sluggable(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name') // Assuming 'name' is the column you want to base the slug on
            ->saveSlugsTo('slug');
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}
