<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceParameter extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'value',
        'active'
    ];

    protected $searchableFields = ['*'];

    protected $table = 'service_parameters';

    protected $casts = [
        'active' => 'boolean'
    ];
}
