<?php

namespace App\Models\Building;

use App\Models\Building\FlatTenant;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Complaint extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'complaintable_type',
        'complaintable_id',
        'user_id',
        'complaint_type',
        'category',
        'open_time',
        'close_time',
        'photo',
        'remarks',
        'status',
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'open_time' => 'datetime',
        'close_time' => 'datetime',
        'photo' => 'array',
        'remarks' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function complaintable()
    {
        return $this->morphTo(FlatTenant::class, Building::class);
    }
}
