<?php

namespace App\Models\Vendor;

use App\Models\Scopes\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorEscalationMatrix extends Model
{
    use HasFactory;
    use Searchable;

    protected $table = 'vendor_escalation_matrix';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'position',
        'escalation_level',
        'vendor_id',
        'active'
    ];

    protected $searchableFields = ['*'];

    public function vendors()
    {
        return $this->belongsTo(Vendor::class);
    }
}
