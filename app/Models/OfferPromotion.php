<?php

namespace App\Models;

use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferPromotion extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'name',
        'image',
        'description',
        'start_date',
        'end_date',
        'link',
        'building_id',
        'active'
    ];

    protected $table = 'offer_promotions';

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

}
