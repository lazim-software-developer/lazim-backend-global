<?php

namespace App\Models;

use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentOwner extends Model
{
    use HasFactory;

    protected $table = 'apartment_owners';

    protected $fillable = ['owner_number', 'email', 'name', 'mobile', 'passport', 'emirates_id', 'trade_license', 'flat_id'];

    public function flats() {
        return $this->hasMany(Flat::class);
    }
}
