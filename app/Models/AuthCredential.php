<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthCredential extends Model
{
    use HasFactory;

    const TALLY_MODULE = "TALLY";
    protected $table = 'auth_credentials';

    protected $fillable = [
        'client_id',
        'api_key',
        'module',
        'owner_association_id',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class, 'owner_association_id');
    }
}
