<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserApproval extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'document_type', 'status', 'remarks', 'document', 'updated_by','emirates_document','passport'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
