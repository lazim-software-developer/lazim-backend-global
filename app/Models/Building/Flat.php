<?php

namespace App\Models\Building;

use App\Models\User\User;
use App\Models\FlatOwners;
use App\Models\Forms\Guest;
use App\Models\LegalNotice;
use App\Models\MollakTenant;
use App\Models\UserApproval;
use App\Models\Forms\SaleNOC;
use App\Models\ApartmentOwner;
use App\Models\CoolingAccount;
use App\Models\Forms\MoveInOut;
use App\Mail\OaUserRegistration;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\OwnerAssociation;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\Building\FlatTenant;
use App\Models\Visitor\FlatVisitor;
use App\Models\Accounting\OAMInvoice;
use App\Models\Accounting\OAMReceipts;
use Illuminate\Database\Eloquent\Model;
use App\Models\Visitor\FlatDomesticHelp;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flat extends Model
{
    use HasFactory;
    use Searchable;
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'property_number',
        'floor',
        'building_id',
        'description',
        'mollak_property_id',
        'property_type',
        'owner_association_id',
        'suit_area',
        'actual_area',
        'balcony_area',
        'applicable_area',
        'virtual_account_number',
        'parking_count',
        'plot_number',
        'resource',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $searchableFields = ['*'];

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function CreatedBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function tenants()
    {
        return $this->hasMany(FlatTenant::class);
    }
    public function domesticHelps()
    {
        return $this->hasMany(FlatDomesticHelp::class);
    }
    public function visitors()
    {
        return $this->hasMany(FlatVisitor::class);
    }
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'flat_owner',
            'flat_id',
            'owner_id'
        );
    }
    public function oaUserRegistration()
    {
        return $this->belongsTo(OaUserRegistration::class);
    }

    public function owners()
    {
        return $this->belongsToMany(ApartmentOwner::class, 'flat_owner', 'flat_id', 'owner_id')->where('active', 1);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function mollakTenants()
    {
        return $this->hasMany(MollakTenant::class);
    }
    public function moveinOut()
    {
        return $this->hasMany(MoveInOut::class);
    }
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }
    public function fitOut()
    {
        return $this->hasMany(FitOutForm::class);
    }
    public function accessCard()
    {
        return $this->hasMany(AccessCard::class);
    }
    public function saleNoc()
    {
        return $this->hasMany(SaleNOC::class);
    }
    public function oaminvoices()
    {
        return $this->hasMany(OAMInvoice::class);
    }

    public function oamreceipts()
    {
        return $this->hasMany(OAMReceipts::class);
    }

    public function coolingAccounts()
    {
        return $this->hasMany(CoolingAccount::class);
    }

    public function userApprovals()
    {
        return $this->hasMany(UserApproval::class);
    }

    public function legalNotices()
    {
        return $this->hasMany(LegalNotice::class);
    }
    public function flatOwners()
    {
        return $this->hasMany(FlatOwners::class, 'flat_id');
    }
}
