<?php

namespace App\Models\Master;

use App\Models\Building\Building;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;
    use Searchable;

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
}
