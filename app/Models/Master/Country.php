<?php

namespace App\Models\Master;

use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\OaUserRegistration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use Searchable;

    protected $connection = 'mysql';

    protected $fillable = ['name'];

    protected $searchableFields = ['*'];

    public function buildings()
    {
        return $this->hasMany(Building::class);
    }
     public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }
    public function owner()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
}

