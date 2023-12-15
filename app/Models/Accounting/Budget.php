<?php

namespace App\Models\Accounting;

use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use App\Models\Accounting\Budgetitem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'owner_association_id',
        'budget_period',
        'budget_from',
        'budget_to',
    ];


    // Define an inverse one-to-many relationship with OwnerAssociation
    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }

    // Define an inverse one-to-many relationship with Building
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function budgetitems()
    {
        return $this->hasMany(Budgetitem::class);
    }

    public function tenders() {
        return $this->hasMany(Tender::class);
    }
}
