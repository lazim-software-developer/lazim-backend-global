<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'subject', 'body', 'owner_association_id'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
}
