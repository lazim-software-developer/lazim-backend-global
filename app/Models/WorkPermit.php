<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Building\FacilityBooking;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkPermit extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $fillable = [
        'name','icon','active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->morphMany(FacilityBooking::class, 'bookable');
    }
}
