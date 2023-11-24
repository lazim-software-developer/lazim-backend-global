<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OAMReceipts extends Model
{
    use HasFactory;

    protected $table = 'oam_receipts';

    // Fillable fields 
    protected $fillable = [
        'receipt_number',
        'receipt_date',
        'record_source',
        'receipt_amount',
        'receipt_created_date',
        'transaction_reference',
        'payment_mode',
        'virtual_account_description',
        'noqodi_info',
        'payment_status',
        'from_date',
        'to_date',
        'building_id',
        'flat_id'
    ];
}
