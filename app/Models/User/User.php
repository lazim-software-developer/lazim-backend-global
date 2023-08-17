<?php

namespace App\Models\User;

use App\Models\Master\Role;
use App\Models\Vendor\Vendor;
use App\Models\Building\Document;
use App\Models\Building\BuildingPoc;
use App\Models\Vendor\Attendance;
use App\Models\Building\Complaint;
use App\Models\Building\FacilityBooking;
use App\Models\Building\FlatTenant;
use App\Models\Visitor\FlatVisitor;
use App\Models\Building\Flat;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Scopes\Searchable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
    ];

    protected $searchableFields = ['*'];

    protected $hidden = ['password'];

    protected $casts = [
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'active' => 'boolean',
    ];


    public function canAccessFilament(): bool
    {
        return true;
    }
    
    public function getFilamentName(): string

        {
    
            return $this->fullName;
    
    }

    public function getFullNameAttribute(): string
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
}
