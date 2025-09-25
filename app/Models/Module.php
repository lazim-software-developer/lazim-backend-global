<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get all owner associations linked to this module.
     */
    public function ownerAssociations()
    {
        return $this->belongsToMany(OwnerAssociation::class, 'module_owner_association', 'module_id', 'owner_association_id')
        ->withPivot('created_by', 'updated_by') 
        ->withTimestamps();
    }
}
