<?php

namespace App\Models\Vendor;

use App\Models\Asset;
use App\Models\Building\Building;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PPM extends Model
{
    use HasFactory;

    protected $table = 'ppm';

    protected $fillable = [
        'quarter',
        'date',
        'job_description',
        'document',
        'created_by',
        'status',
        'remarks',
        'building_id',
        'asset_id',
        'status_updated_by',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
