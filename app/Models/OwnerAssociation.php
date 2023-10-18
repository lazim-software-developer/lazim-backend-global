<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerAssociation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'email', 'trn_number', 
        'address', 'mollak_id', 'verified', 'verified_by','active'
    ];

    public function users() {
        return $this->hasMany(User::class);
    }
}
