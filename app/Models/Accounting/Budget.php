<?php

namespace App\Models\Accounting;

use App\Models\Building\Building;
use App\Models\OwnerAssociation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'budgetable_type',
        'budgetable_id',
        'budget_excl_vat',
        'vat_rate',
        'vat_amount',
        'total',
        'rate',
        'building_id',
        'owner_association_id',
        'budget_period',
        'budget_from',
        'budget_to',
    ];

    /**
     * Get the parent budgetable model (category, subcategory, or service).
     */
    public function budgetable()
    {
        return $this->morphTo();
    }

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
}
