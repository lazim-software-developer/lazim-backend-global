<?php

namespace App\Models\Building;

use App\Models\CoolingAccount;
use App\Models\User\User;
use App\Models\Forms\Guest;
use App\Models\MollakTenant;
use App\Models\Forms\SaleNOC;
use App\Models\ApartmentOwner;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Building\Building;
use App\Models\Scopes\Searchable;
use App\Models\OaUserRegistration;
use App\Models\Building\FlatTenant;
use App\Models\Visitor\FlatVisitor;
use App\Models\Accounting\OAMInvoice;
use Illuminate\Database\Eloquent\Model;
use App\Models\Visitor\FlatDomesticHelp;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flat extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = ['property_number', 'floor', 'building_id', 'description', 'mollak_property_id', 'property_type', 'owner_association_id'];

    protected $searchableFields = ['*'];

    public function building()
    {
        return $this->belongsTo(Building::class);
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

    public function owners() {
        return $this->belongsToMany(ApartmentOwner::class, 'flat_owner', 'flat_id', 'owner_id');
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function mollakTenants() {
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

    public function coolingAccounts()
    {
        return $this->hasMany(CoolingAccount::class);
    }
}
