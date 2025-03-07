<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'address',
        'state',
        'number_of_communities',
        'number_of_units',
        'message',
        'features',
        'onboarding_assistance',
        'support',
    ];

    protected $casts = [
        'features' => 'json',
        'onboarding_assistance' => 'json',
        'support' => 'json',
    ];
}
