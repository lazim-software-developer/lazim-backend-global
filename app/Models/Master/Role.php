<?php

namespace App\Models\Master;

use App\Models\User\User;
use Spatie\Permission\Models;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\OaUserRegistration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name','is_active'];

    protected $searchableFields = ['*'];

    // public function users()
    // {
    //     return $this->hasMany(User::class);
    // }

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function building()
    {
        return $this->belongsToMany(Building::class, 'building_roles','role_id','building_id');
    }

     public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }

}
