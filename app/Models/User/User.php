<?php

namespace App\Models\User;

use App\Models\AccountCredentials;
use Filament\Panel;
use App\Models\Asset;
use App\Models\Vendor\PPM;
use App\Models\Forms\Guest;
use App\Models\Master\Role;
use App\Models\Building\Flat;
use App\Models\Forms\SaleNOC;
use App\Models\ItemInventory;
use App\Models\Vendor\Vendor;
use App\Models\Accounting\WDA;
use App\Models\Community\Poll;
use App\Models\Community\Post;
use App\Models\Forms\MoveInOut;
use App\Models\ResidentialForm;
use App\Models\Forms\AccessCard;
use App\Models\Forms\FitOutForm;
use App\Models\OwnerAssociation;
use App\Models\TechnicianAssets;
use App\Models\TechnicianVendor;
use App\Models\Building\Building;
use App\Models\Building\Document;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\Attendance;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Accounting\Invoice;
use App\Models\Building\Complaint;
use App\Models\Community\PostLike;
use App\Models\Building\FlatTenant;
use App\Models\Visitor\FlatVisitor;
use App\Models\Building\BuildingPoc;
use App\Models\ExpoPushNotification;
use App\Models\Community\PollResponse;
use Filament\Models\Contracts\HasName;
use Laravel\Jetstream\HasProfilePhoto;
use App\Models\Building\FacilityBooking;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Facades\Filament;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasTenants, HasName
{
    use HasRoles;
    // use HasPanelShield;
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
        'email_verified',
        'phone_verified',
        'active',
        'lazim_id',
        'role_id',
        'building_id',
        'owner_association_id',
        'profile_photo',
        'owner_id'
    ];

    protected $searchableFields = ['*'];

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'active'         => 'boolean',
    ];

    // public function getFilamentName(): string
    // {
    //     return $this->fullName;
    // }
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_photo ? env('AWS_URL') . '/' . $this->profile_photo : env('DEFAULT_AVATAR');
    }
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
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
        $allowedRoles = ['Admin','Technician', 'Security', 'Tenant', 'Owner', 'Managing Director', 'Vendor'];

        // Retrieve the role name using the provided method
        $userRoleName = Role::find($this->role_id)->name;
        if ($panel->getId() === 'app' && $userRoleName == 'Admin') {
            return true;
        }
        else if($panel->getId() === 'admin' && !in_array($userRoleName, $allowedRoles) && $this->active) {
            return true;
        }
        else{
            Filament::auth()->logout();
            throw ValidationException::withMessages([
                'data.email' => __('Unautorized access'),
            ]);
        }
    }

    public function ownerAssociation(): BelongsToMany
    {
        return $this->belongsToMany(OwnerAssociation::class);
    }

    public function getTenants(Panel $panel): Collection
    {
        // return OwnerAssociation::where('id',$this->ownerAssociation)->get();
        return $this->ownerAssociation()->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->ownerAssociation()->whereKey($tenant)->exists();
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

    public function technicianVendors()
    {
        return $this->hasMany(TechnicianVendor::class, 'technician_id')->where('active', true);
    }
    public function wdas()
    {
        return $this->hasMany(WDA::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'technician_assets', 'technician_id');
    }
    public function ppms()
    {
        return $this->hasMany(PPM::class);
    }

    public function assignees()
    {
        return $this->hasMany(Complaint::class, 'technician_id');
    }

    public function expoNotification()
    {
        return $this->hasOne(ExpoPushNotification::class);
    }

    public function poll()
    {
        return $this->hasMany(Poll::class);
    }

    public function pollResponse()
    {
        return $this->hasMany(PollResponse::class);
    }
    // public function roles(): BelongsToMany
    // {
    //     return $this->belongsToMany(Building::class, 'owner_committees', 'building_id', 'user_id');
    // }
    public function iteminventory()
    {
        return $this->hasMany(ItemInventory::class);
    }
      public function accountcredentials()
    {
        return $this->hasMany(AccountCredentials::class,'created_by');
    }
}
