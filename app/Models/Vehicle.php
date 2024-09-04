<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = ['user_id', 'vehicle_number', 'makani_number','flat_id', 'owner_association_id'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
