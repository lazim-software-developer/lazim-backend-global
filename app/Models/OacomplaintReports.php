<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OacomplaintReports extends Model
{
    use HasFactory;

    protected $table = 'oacomplaint_reports';

    protected $fillable = [
        'type',
        'user_id',
        'building_id',
        'issue',
        'image',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
