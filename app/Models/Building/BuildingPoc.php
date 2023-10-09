<?php

namespace App\Models\Building;

use App\Models\Building\Building;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BuildingPoc extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'building_id',
        'user_id',
        'oa_user_registration_id',
        'role_name',
        'escalation_level',
        'active',
        'emergency_contact',
    ];

    protected $searchableFields = ['*'];

    protected $table = 'building_pocs';

    protected $casts = [
        'active' => 'boolean',
        'emergency_contact' => 'boolean',
    ];
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }
}
