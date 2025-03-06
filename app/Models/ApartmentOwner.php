<?php

namespace App\Models;


use App\Models\FlatOwners;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartmentOwner extends Model
{
    use HasFactory,SoftDeletes;

    protected $connection = 'mysql';

    protected $table = 'apartment_owners';

    protected $fillable = ['owner_number', 'email', 'name', 'mobile', 'passport', 'emirates_id', 'trade_license', 'flat_id','owner_association_id'
    ,'building_id','owner_status','resource','primary_owner_mobile','primary_owner_email','deleted_at','created_by','updated_by'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function flatOwners() {
        return $this->hasMany(FlatOwners::class, 'owner_id');
    }
    
    public function flats() {
        return $this->hasMany(FlatOwners::class, 'flat_id');
    }

    public function users(){
        return $this->hasMany(User::class,'owner_id');
    }
}
