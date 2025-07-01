<?php

namespace App\Models;

use App\Models\OwnerAssociation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleNocNotification extends Model
{
    protected $connection = 'mysql';
    // protected $table = 'notifications_sents';

    protected $fillable = [
        'building_id',
        'user_id',
        'sale_noc_id',
        'title',
        'notification_message',
        'is_read',
        'read_by',
        'read_at',
        'owner_association_id'
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
}
