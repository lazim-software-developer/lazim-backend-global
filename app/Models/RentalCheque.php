<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalCheque extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_detail_id',
        'cheque_number',
        'amount',
        'due_date',
        'status',
        'status_updated_by',
        'mode_payment',
        'cheque_status',
        'payment_link',
        'comments',
    ];

    protected $casts = [
        'comments' => 'array',
    ];

    public function rentalDetail()
    {
        return $this->belongsTo(RentalDetail::class);
    }

    public function statusUpdatedBy()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }
}
