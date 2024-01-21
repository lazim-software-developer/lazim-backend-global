<?php

namespace App\Models;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralFund extends Model
{
    use HasFactory;

    protected $fillable = [
        'statement_date',
        'date',
        'description',
        'debited_amount',
        'credited_amount',
        'type',
        'building_id'
    ];


    public function building(){
        return $this->belongsTo(Building::class);
    }
}
