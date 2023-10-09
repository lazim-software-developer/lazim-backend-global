<?php

namespace App\Models;

use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Models\Building\Document;
use App\Models\Building\FacilityBooking;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\OaDetails;
use App\Models\User\User;
use App\Models\Vendor\Attendance;
use App\Models\Vendor\Contact;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatDomesticHelp;
use App\Models\Visitor\FlatVisitor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OaUserRegistration extends Model
{
    use HasFactory;
    protected $fillable = [
        'oa_id',
        'name',
        'email',
        'phone',
        'trn',
        'address',
        'verified',
        'verified_by'
    ];

    protected $searchableFields = ['*'];

    protected $table = 'oa_user_registration';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function oaDetails()
    {
        return $this->hasMany(OaDetails::class);
    }
    public function building()
    {
        return $this->belongsTo(Building::class);
    }
    public function members()
    {
        return $this->belongsToMany(User::class);
    }

    public function users():BelongsToMany
    {
        return $this->belongsToMany(User::class);

    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
     public function buildingPocs()
    {
        return $this->hasMany(BuildingPoc::class);
    }
     public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
    public function facilitybookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }
    public function flats()
    {
        return $this->hasMany(Flat::class);
    }
    public function flatTenants()
    {
        return $this->hasMany(FlatTenant::class);
    }
    public function flatdomestichelps()
    {
        return $this->hasMany(FlatDomesticHelp::class);
    }
    public function flatvisitors()
    {
        return $this->hasMany(FlatVisitor::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function vednors()
    {
        return $this->hasMany(Vendor::class);
    }
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }
    public function buildings()
    {
        return $this->hasMany(Building::class);
    }
}
