<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use App\Models\Visitor\FlatVisitor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
    protected $fillable = [
        'passport_number',
        'visa_validity_date',
        'stay_duration',
        'expiry_date',
        'access_card_holder',
        'original_passport',
        'guest_registration',
        'flat_visitor_id',
        'dtmc_license_url',
        'owner_association_id',
        'status',
        'guest_name',
        'holiday_home_name',
        'emergency_contact',
        'remarks',
        'rejected_fields'
    ];

    protected $searchableFields = ['*'];
    protected $casts = [
        'access_card_holder' => 'boolean',
        'original_passport' => 'boolean',
        'guest_registration' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function flatVisitor()
    {
        return $this->belongsTo(FlatVisitor::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
