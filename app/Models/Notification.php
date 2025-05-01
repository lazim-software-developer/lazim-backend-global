<?php

namespace App\Models;

use App\Models\OwnerAssociation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    protected $connection = 'mysql';
    // protected $table = 'notifications_sents';

    protected $fillable = [
        'read_at',
    ];

    protected $casts = [
        'id' => 'string',
        'data' => 'array',
        'custom_json_data' => 'array',
    ];

    public function ownerAssociation()
    {
        // Assuming the ID is stored in custom_json_data->owner_association_id
        return $this->belongsTo(
            OwnerAssociation::class, 
            'custom_json_data->owner_association_id', 
            'id'
        );
    }
}
