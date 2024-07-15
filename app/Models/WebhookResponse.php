<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookResponse extends Model
{
    use HasFactory;

    protected $fillable = ['management_company_id', 'type', 'response'];
}
