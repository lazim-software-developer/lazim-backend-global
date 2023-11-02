<?php

namespace App\Models\User;


use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Models\Building\Document;
use App\Models\Building\FacilityBooking;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\Community\Post;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\Forms\MoveInOut;
use App\Models\Forms\Guest;
use App\Models\Forms\SaleNOC;
use App\Models\Master\Role;
use App\Models\OaUserRegistration;
use App\Models\OwnerAssociation;
use App\Models\ResidentialForm;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\Attendance;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatVisitor;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasName
{
    use Notifiable;
    use HasFactory;
    use Searchable;
    use HasApiTokens;
    use HasProfilePhoto;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'phone_verified',
        'active',
        'lazim_id',
        'role_id',
        'building_id',
        'owner_association_id',
        'profile_photo'
    ];

    protected $searchableFields = ['*'];

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'active'         => 'boolean',
    ];

    public function getFilamentName(): string
    {

        return $this->fullName;
    }

    public function getFullNameAttribute(): string
    {

        return "{$this->first_name} {$this->last_name}";
    }

    public function oaUserRegistration()
    {
        return $this->hasMany(OaUserRegistration::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class, 'owner_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'accepted_by');
    }
    // public function oaUser()
    // {
    //     return $this->hasMany(OaUserRegistration::class);
    // }
    public function pocs()
    {
        return $this->hasMany(BuildingPoc::class);
    }

    public function attendanceApproves()
    {
        return $this->hasMany(Attendance::class, 'approved_by');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function bookings()
    {
        return $this->hasMany(FacilityBooking::class);
    }

    public function userFacilityBookingApproves()
    {
        return $this->hasMany(FacilityBooking::class, 'approved_by');
    }

    public function tenants()
    {
        return $this->hasMany(FlatTenant::class, 'tenant_id');
    }

    public function flatVisitorInitates()
    {
        return $this->hasMany(FlatVisitor::class, 'initiated_by');
    }

    public function flatVisitorsApproves()
    {
        return $this->hasMany(FlatVisitor::class, 'approved_by');
    }

    public function vendorApprovals()
    {
        return $this->belongsToMany(
            Vendor::class,
            'vendor_approval',
            'approved_by'
        );
    }

    public function flats()
    {
        return $this->belongsToMany(Flat::class, 'flat_owner', 'owner_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_employee');
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->email, config('auth.super_admins'));
    }
    public function canAccessPanel(Panel $panel): bool
    {
        if($this->role_id == 10 || $this->role_id == 9)
        {
            return true;
        }
        return false;
    }

    public function ownerAssociation()
    {
        return $this->belongsTo(OwnerAssociation::class);
    }

    public function residences()
    {
        return $this->belongsToMany(Flat::class, 'flat_tenants', 'tenant_id');
    }

    public function likedPosts()
    {
        return $this->hasMany(PostLike::class);
    }
    public function Posts()
    {
        return $this->hasMany(Post::class);
    }
    public function moveinOut()
    {
        return $this->hasMany(MoveInOut::class);
    }
    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function nocForms()
    {
        return $this->hasMany(Guest::class);
    }

    public function userDocuments()
    {
        return $this->hasMany(Document::class, 'documentable_id');
    }
    public function fitOut()
    {
        return $this->hasMany(FitOutForm::class);
    }
    public function accessCard()
    {
        return $this->hasMany(AccessCard::class);
    }
    public function residentialForm()
    {
        return $this->hasMany(ResidentialForm::class);
    }
    public function guestRegsitration()
    {
        return $this->hasMany(Guest::class);
    }
    public function moveinData()
    {
        return $this->hasMany(MoveInOut::class);
    }
    public function saleNoc()
    {
        return $this->hasMany(SaleNOC::class);
    }
}
