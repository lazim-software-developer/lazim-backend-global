<?php

namespace App\Models;

use App\Models\Forms\SaleNOC;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerAssociation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'trn_number',
        'address', 'mollak_id', 'verified', 'verified_by', 'active'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function saleNoc()
    {
        return $this->hasMany(SaleNOC::class);
    }

    // Define a one-to-many relationship with Budget
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}
