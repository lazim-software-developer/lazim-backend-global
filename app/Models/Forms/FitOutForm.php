<?php

namespace App\Models\Forms;

use App\Models\Building\Building;
use App\Models\Building\Flat;
use App\Models\FitOutFormContractorRequest;
use App\Models\Order;
use App\Models\OwnerAssociation;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitOutForm extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'contractor_name',
        'flat_id',
        'phone',
        'email',
        'no_objection',
        'undertaking_of_waterproofing',
        'building_id',
        'user_id',
        'owner_association_id',
        'status',
        'remarks',
        'rejected_fields',
        'admin_document',
        'ticket_number',
        'payment_link'
    ];

    protected $searchableFields = ['*'];
    protected $casts = [
        'no_objection'         => 'boolean',
        'undertaking_of_waterproofing'         => 'boolean',
    ];

    /**
     * Resolve the route binding for the model.
     *
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $data = is_numeric($value) ? $value : decrypt($value);
        return $this->where('id',$data)->firstOrFail();
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contractorRequest()
    {
        return $this->hasOne(FitOutFormContractorRequest::class);
    }

    public function orders()
    {
        return $this->morphMany(Order::class, 'orderable');
    }

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
}
