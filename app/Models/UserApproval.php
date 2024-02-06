<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserApproval extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'document_type', 'status', 'remarks', 'document', 'updated_by'];
}
