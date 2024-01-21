<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\ItemInventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;
    protected $fillable = ['name','quantity','building_id','description'];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function iteminventory()
    {
        return $this->hasMany(ItemInventory::class);
    }
}
