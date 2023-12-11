<?php

namespace App\Models\Accounting;

use App\Models\User\User;
use App\Models\Vendor\Vendor;
use App\Models\Master\Service;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use App\Models\Accounting\Proposal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'created_by', 'building_id', 'budget_id', 'owner_association_id', 'end_date', 'document', 'service_id'];

    // Tender has many services through TenderService
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Tender has many vendors through TenderVendor
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'tender_vendors');
    }

    // Tender belongs to a building
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    // Tender belongs to a budget
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    // Tender belongs to an owner association
    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }

    // Tender is created by a user
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }
}
