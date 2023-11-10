<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    use HasFactory;

    // Tender has many services through TenderService
    public function services()
    {
        return $this->belongsToMany(Service::class, 'tender_services');
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
}
