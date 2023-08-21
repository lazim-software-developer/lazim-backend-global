<?php

namespace App\Models\Building;

use App\Models\Master\Facility;
use App\Models\Scopes\Searchable;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacilityBooking extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'facility_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'order_id',
        'payment_status',
        'remarks',
        'reference_number',
        'approved',
        'approved_by',
    ];

    protected $searchableFields = ['*'];

    protected $table = 'facility_bookings';

    protected $casts = [
        'date' => 'date',
        'remarks' => 'array',
        'approved' => 'boolean',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userFacilityBookingApprove()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
