<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = ['orderable_id', 'orderable_type', 'payment_status', 'amount', 'payment_intent_id','owner_association_id'];

    public function orderable()
    {
        return $this->morphTo();
    }
}
