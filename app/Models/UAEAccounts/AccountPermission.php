<?php

namespace App\Models\UAEAccounts;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountPermission extends Model
{
    use HasFactory;
    protected $connection = 'lazim_accounts';
    protected $table = 'permissions';

    public $timestamps = false;
}
