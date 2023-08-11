<?php

namespace App\Models\Building;

use App\Models\Building\Flat;
use App\Models\Building\Complaint;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlatTenant extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'flat_id',
        'tenant_id',
        'primary',
        'start_date',
        'end_date',
        'active',
    ];

    protected $searchableFields = ['*'];

    protected $table = 'flat_tenants';

    protected $casts = [
        'primary' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'active' => 'boolean',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function complaints()
    {
        return $this->morphMany(Complaint::class, 'complaintable');
    }
}
