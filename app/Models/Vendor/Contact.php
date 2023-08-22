<?php

namespace App\Models\Vendor;

use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'building_id',
        'designation',
        'contactable_type',
        'contactable_id',
    ];

    protected $searchableFields = ['*'];

    public function contactable()
    {
        return $this->morphTo();
    }
    // public function building()
    // {
    //     return $this->belongsTo(Building::class);
    // }
}
