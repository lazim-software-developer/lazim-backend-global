<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerAssociationUser extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'owner_association_user';

    protected $fillable = ['owner_association_id','user_id','active','from'];

    public $timestamps = false;
}
