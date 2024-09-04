<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class AccountCredentials extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'mail_credentials';
    protected $fillable = [
        'mailer',
        'username',
        'host',
        'port',
        'encryption',
        'email',
        'password',
        'oa_id',
        'created_by',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function ownerassociation()
    {
        return $this->belongsTo(OwnerAssociation::class,'oa_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'created_by');
    }
}
