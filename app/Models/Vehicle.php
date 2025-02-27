<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = ['user_id', 'vehicle_number', 'parking_number','flat_id', 'owner_association_id'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
