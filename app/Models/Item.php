<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\ItemInventory;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;
    protected $fillable = ['name','quantity','building_id','description'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function iteminventory()
    {
        return $this->hasMany(ItemInventory::class);
    }

    public function vendors(){
        return $this->belongsToMany(Vendor::class);
    }
}
