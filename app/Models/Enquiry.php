<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'enquiries';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'company_name'
    ];
}
