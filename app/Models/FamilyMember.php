<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'passport_number',
        'passport_expiry_date',
        'emirates_id',
        'emirates_expiry_date',
        'gender',
        'relation',
        'user_id',
        'owner_association_id'
    ];

    protected $cast = [
        'passport_expiry_date' => 'date',
        'emirates_expiry_date' => 'date',
    ];
}
                             