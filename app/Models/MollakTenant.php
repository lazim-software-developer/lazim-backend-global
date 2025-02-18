<?php

namespace App\Models;

use App\Models\Building\Flat;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MollakTenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'name', 'contract_number', 'emirates_id', 'passport', 'license_number', 'mobile', 'email', 'start_date',
        'end_date', 'contract_status', 'building_id', 'flat_id', 'owner_association_id'
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    // public function ownerAssociation()
    // {
    //     return $this->building->ownerAssociation ?? null;
    // }
}
