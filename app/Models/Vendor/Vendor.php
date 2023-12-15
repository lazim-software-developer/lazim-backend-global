<?php

namespace App\Models\Vendor;

use App\Models\Accounting\Invoice;
use App\Models\Accounting\WDA;
use App\Models\Asset;
use App\Models\OaUserRegistration;
use App\Models\OwnerAssociation;
use App\Models\TechnicianVendor;
use App\Models\User\User;
use App\Models\Master\Service;
use App\Models\Vendor\Contact;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\Contract;
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
        return $this->belongsToMany(Building::class, 'building_vendor', 'vendor_id','building_id')->where('active', true)
                ->withPivot(['contract_id', 'active','start_date','end_date']);
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

    public function technicianVendors()
    {
        return $this->hasMany(TechnicianVendor::class,'vendor_id')->where('active', true);
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class,'vendor_id');
    }
    public function wdas()
    {
        return $this->hasMany(WDA::class,'vendor_id');
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class,'vendor_id');
    }
    public function assets()
    {
        return $this->belongsToMany(Asset::class);
    }

    public function OA()
    {
        return $this->belongsTo(OwnerAssociation::class,'owner_association_id');
    }
}
