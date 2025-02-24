<?php

namespace App\Models\Vendor;

use App\Models\Scopes\Searchable;
use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorManager extends Model
{
    use HasFactory;
    use Searchable;

    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'vendor_id',
    ];

    protected $searchableFields = ['*'];

    public function vendors()
    {
        return $this->belongsTo(Vendor::class);
    }

     public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
