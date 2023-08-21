<?php

namespace App\Models\Master;

use App\Models\Scopes\Searchable;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['name', 'active'];

    protected $searchableFields = ['*'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function services()
    {
        return $this->belongsToMany(Vendor::class);
    }
}
