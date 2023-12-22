<?php

namespace App\Models\Visitor;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\Forms\Guest;
use App\Models\OaUserRegistration;
use App\Models\User\User;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlatVisitor extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'flat_id',
        'name',
        'building_id',
        'phone',
        'type',
        'start_time',
        'end_time',
        'verification_code',
        'initiated_by',
        'approved_by',
        'remarks',
        'email',
        'number_of_visitors',
        'time_of_viewing',
        'status'
    ];

    protected $searchableFields = ['*'];

    protected $table = 'flat_visitors';

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'remarks' => 'array',
    ];
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function userInitiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function userApprovedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }
}
