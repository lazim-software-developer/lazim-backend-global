<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitOutForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'contractor_name',
        'flat_id',
        'phone',
        'email',
        'no_objection',
        'undertaking_of_waterproofing',
        'building_id',
        'user_id',
    ];

    protected $searchableFields = ['*'];
    protected $casts = [
        'allow_postupload'         => 'boolean',
    ];

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
