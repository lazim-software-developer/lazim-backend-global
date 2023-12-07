<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResidentialForm extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'unit_occupied_by',
        'name',
        'building_id',
        'flat_id',
        'passport_number',
        'number_of_adults',
        'number_of_children',
        'office_number',
        'trn_number',
        'passport_expires_on',
        'emirates_id',
        'emirates_expires_on',
        'title_deed_number',
        'user_id',
        'emergency_contact',
        'passport_url',
        'emirates_url',
        'title_deed_url',
        'owner_association_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'emergency_contact' => 'array',
    ];

    // Relationships
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
