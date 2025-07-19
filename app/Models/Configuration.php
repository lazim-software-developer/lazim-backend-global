<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    protected $connection = 'mysql';

    protected $fillable = ['key', 'value', 'owner_association_id'];
}
