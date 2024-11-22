<?php

namespace App\Models;

use App\Models\Building\Flat;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Bill extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'amount',
        'month',
        'type',
        'flat_id',
        'due_date',
        'dewa_number',
        'uploaded_on',
        'status',
        'uploaded_by',
        'status_updated_by',
    ];

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
    public function uploadedBy()
    {
        return $this->belongsTo(User::class,'uploaded_by');
    }
    public function statusUpdatedBy()
    {
        return $this->belongsTo(User::class,'status_updated_by');
    }
}
