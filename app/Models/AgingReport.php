<?php

namespace App\Models;

use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgingReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'flat_id',
        'owner_id',
        'outstanding_balance',
        'balance_1',
        'balance_2',
        'balance_3',
        'balance_4',
        'over_balance',
        'year'
    ];

    public function flat(){
        return $this->belongsTo(Flat::class);
    }

    public function owner(){
        return $this->belongsTo(ApartmentOwner::class);
    }
}
