<?php

namespace App\Models\Master;

use App\Models\Vendor\Vendor;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'building_id','active'];

    protected $searchableFields = ['*'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function services()
    {
        return $this->belongsToMany(Vendor::class);
    }
    public function building()
    {
        return $this->belongsToMany(Building::class, 'building_services','service_id','building_id');
    }
}
