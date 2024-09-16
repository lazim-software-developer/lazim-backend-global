<?php

namespace App\Models;

use App\Models\Building\Flat;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserApproval extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = ['user_id', 'document_type', 'status', 'remarks', 'document', 'updated_by', 'emirates_document', 'passport', 'owner_association_id', 'flat_id'];

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

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }
}
