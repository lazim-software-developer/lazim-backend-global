<?php

namespace App\Models\Accounting;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\OwnerAssociation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OAMReceipts extends Model
{
    use HasFactory;
    protected $connection = 'mysql';

    protected $table = 'oam_receipts';

    // Fillable fields
    protected $fillable = [
        'receipt_number',
        'receipt_date',
        'receipt_period',
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
        'flat_id',
        'owner_association_id',
    ];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
