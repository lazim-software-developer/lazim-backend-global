<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{

    protected $connection = 'mysql';

    const OA_TYPE = 'globalOa';

    protected $fillable = [
        'payment_day', 'follow_up_day'
    ];
}
