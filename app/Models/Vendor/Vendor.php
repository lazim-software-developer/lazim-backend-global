<?php

namespace App\Models\Vendor;

use App\Models\OaUserRegistration;
use App\Models\User\User;
use App\Models\Master\Service;
use App\Models\Vendor\Contact;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\VendorEscalationMatrix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'name',
        'owner_id',
        'building_id',
        'tl_number',
        'tl_expiry',
        'status',
        'remarks',
        'owner_association_id',
        'phone',
        'address_line_1',
        'address_line_2',
        'landline_number',
        'website',
        'fax',
    ];

    protected $searchableFields = ['*'];

    protected $casts = [
        'tl_expiry' => 'date',
        'remarks' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function vendorApprovals()
    {
        return $this->belongsToMany(
            User::class,
            'vendor_approval',
            'vendor_id',
            'approved_by'
        );
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    public function employees()
    {
        return $this->belongsToMany(User::class, 'vendor_employee');
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function buildings()
    {
        return $this->belongsToMany(Building::class, 'building_vendor', 'building_id','vendor_id');
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }

    public function managers()
    {
        return $this->hasMany(VendorManager::class);
    }

    public function escalationMatrix()
    {
        return $this->hasMany(VendorEscalationMatrix::class);
    }
}
