<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoveInOut extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'approved',
        'building_id',
        'approved_id',
        'flat_id',
        'type',
        'preference',
        'moving_date',
        'moving_time',
        'handover_acceptance',
        'receipt_charges',
        'contract',
        'title_deed',
        'passport',
        'dewa',
        'cooling_registration',
        'gas_registration',
        'vehicle_registration',
        'movers_license',
        'movers_liability',
    ];

    protected $searchableFields = ['*'];
    
    protected $casts = [
        'allow_postupload' => 'boolean',
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

    public function media()
    {
        return $this->morphMany(Media::class, 'mediaable');
    }
}
