<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
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
        'dtmc_license',
    ];

    protected $searchableFields = ['*'];
    protected $casts = [
        'allow_postupload'         => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
