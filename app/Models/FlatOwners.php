<?php

namespace App\Models;

use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class FlatOwners extends Pivot
{
    use HasFactory;

    protected $table = 'flat_owner';

    protected $fillable = ['flat_id', 'owner_id', 'active'];

    public function apartmentowner(): BelongsTo
    {
        return $this->belongsTo(ApartmentOwner::class);
    }
    
    public function flat(): BelongsTo
    {
        return $this->belongsTo(Flat::class);
    }
}
