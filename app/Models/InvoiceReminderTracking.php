<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class InvoiceReminderTracking extends Model
{

    protected $connection = 'mysql';

    const OA_TYPE = 'globalOa';

    protected $fillable = [
        'invoice_id', 'user_id', 'invoice_number', 'invoice_amount',
        'invoice_actual_date', 'user_email', 'building_id', 'flat_id', 'created_at', 'updated_at'
    ];
}
