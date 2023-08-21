<?php

namespace App\Models\Vendor;

use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'building_id',
        'user_id',
        'date',
        'entry_time',
        'exit_time',
        'attendance',
        'approved_by',
        'approved_on',
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'date' => 'date',
        'attendance' => 'boolean',
        'approved_on' => 'datetime',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function userAttendance()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userAttendanceApprove()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
