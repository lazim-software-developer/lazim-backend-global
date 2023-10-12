<?php

namespace App\Models\Building;

use App\Models\ApartmentOwner;
use App\Models\Building\Building;
use App\Models\Building\FlatTenant;
use App\Models\FlatOwner;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use App\Models\Visitor\FlatDomesticHelp;
use App\Models\Visitor\FlatVisitor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flat extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['property_number', 'floor', 'building_id', 'description', 'mollak_property_id', 'property_type'];

    protected $searchableFields = ['*'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function tenants()
    {
        return $this->hasMany(FlatTenant::class);
    }
    public function domesticHelps()
    {
        return $this->hasMany(FlatDomesticHelp::class);
    }
    public function visitors()
    {
        return $this->hasMany(FlatVisitor::class);
    }
    // public function users()
    // {
    //     return $this->belongsToMany(
    //         User::class,
    //         'flat_owner',
    //         'flat_id',
    //         'owner_id'
    //     );
    // }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }

    public function owners() {
        return $this->belongsToMany(ApartmentOwner::class, 'flat_owner', 'flat_id', 'owner_id');
    }
}
