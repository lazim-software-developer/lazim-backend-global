<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'mobile',
        'email',
        'card_type',
        'reason',
        'parking_details',
        'occupied_by',
        'tenancy',
        'vehicle_registration',
        'flat_id',
        'user_id',
        'building_id',
        'owner_association_id',
        'status',
        'remarks',
        'rejected_fields'
    ];

    protected $searchableFields = ['*'];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
