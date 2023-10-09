<?php

namespace App\Models\Visitor;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\OaUserRegistration;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlatDomesticHelp extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'flat_id',
        'first_name',
        'last_name',
        'phone',
        'building_id',
        'profile_photo',
        'start_date',
        'end_date',
        'role_name',
        'active',
    ];

    protected $searchableFields = ['*'];

    protected $table = 'flat_domestic_helps';

    protected $casts = [
        'profile_photo' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }
}
