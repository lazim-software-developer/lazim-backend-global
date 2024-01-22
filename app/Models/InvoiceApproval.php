<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceApproval extends Model
{
    use HasFactory;
    protected $fillable = ['invoice_id','status','updated_by','remarks'];
}
