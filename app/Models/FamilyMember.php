<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\Building\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FamilyMember extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'passport_number',
        'passport_expiry_date',
        'emirates_id',
        'emirates_expiry_date',
        'gender',
        'relation',
        'user_id',
        'owner_association_id',
        'flat_id',
        'building_id'
    ];

    protected $cast = [
        'passport_expiry_date' => 'date',
        'emirates_expiry_date' => 'date',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }

    public function resident()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
