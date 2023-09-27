<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\OaDetails;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OaUserRegistration extends Model
{
    use HasFactory;
    protected $fillable = [
        'oa_id',
        'name',
        'email',
        'phone',
        'trn',
        'address',
        'verified',
        'verified_by'
    ];

    protected $searchableFields = ['*'];

    protected $table = 'oa_user_registration';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function oaDetails()
    {
        return $this->hasMany(OaDetails::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
