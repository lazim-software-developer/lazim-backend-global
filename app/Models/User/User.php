<?php

namespace App\Models\User;

use App\Mail\OaUserRegistration;
use App\Models\Building\Building;
use App\Models\Building\BuildingPoc;
use App\Models\Building\Complaint;
use App\Models\Building\Document;
use App\Models\Building\FacilityBooking;
use App\Models\Building\Flat;
use App\Models\Building\FlatTenant;
use App\Models\Master\Role;
use App\Models\OaDetails;
use App\Models\Scopes\Searchable;
use App\Models\Vendor\Attendance;
use App\Models\Vendor\Vendor;
use App\Models\Visitor\FlatVisitor;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasName, HasTenants
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
    public function oaUser()
    {
        return $this->hasMany(OaUserRegistration::class);
    }
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
        return str_ends_with($this->role_id, Role::where('name', 'Admin')->value('id'));
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->building;
    }

    public function building(): BelongsToMany
    {
        return $this->belongsToMany(Building::class);
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->building->contains($tenant);
    }

}
