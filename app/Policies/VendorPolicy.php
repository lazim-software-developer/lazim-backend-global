<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Vendor\Vendor;
use Illuminate\Auth\Access\HandlesAuthorization;

class VendorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the vendor can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the vendor can view the model.
     */
    public function view(User $user, Vendor $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the vendor can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the vendor can update the model.
     */
    public function update(User $user, Vendor $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the vendor can delete the model.
     */
    public function delete(User $user, Vendor $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the vendor can restore the model.
     */
    public function restore(User $user, Vendor $model): bool
    {
        return false;
    }

    /**
     * Determine whether the vendor can permanently delete the model.
     */
    public function forceDelete(User $user, Vendor $model): bool
    {
        return false;
    }
}
