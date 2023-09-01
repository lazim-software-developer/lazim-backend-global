<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OaServiceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'service_parameter_id',
        'property_group',
        'from_date',
        'to_date',
        'status',
        'uploaded_by',
    ];

    protected $searchableFields = ['*'];

    protected $table = 'oa_service_requests';
    
    public function serviceParameter()
    {
        return $this->belongsTo(ServiceParameter::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
