<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuleRegulation extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'building_id',
        'rule_regulation',
    ];
}
