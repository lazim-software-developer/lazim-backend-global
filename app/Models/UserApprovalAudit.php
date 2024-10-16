<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserApprovalAudit extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = ['document_type', 'status', 'remarks', 'document', 'updated_by', 'emirates_document', 'passport', 'owner_association_id','user_approval_id'];
    
    public function userApproval()
    {
        return $this->belongsTo(UserApproval::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
}
