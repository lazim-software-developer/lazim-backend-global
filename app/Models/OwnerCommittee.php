<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\Building\Building;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OwnerCommittee extends Model
{
    use HasFactory;

    protected $fillable = ['building_id','user_id','active'];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function building(){
        return $this->belongsTo(Building::class);
    }
}
