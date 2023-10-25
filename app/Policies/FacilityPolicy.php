<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Master\Facility;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacilityPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the facility can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the facility can view the model.
     */
    public function view(User $user, Facility $model): bool
    {
        return true;
    }

    /**
     * Determine whether the facility can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the facility can update the model.
     */
    public function update(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the facility can delete the model.
     */
    public function delete(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the facility can restore the model.
     */
    public function restore(User $user, Facility $model): bool
    {
        return false;
    }

    /**
     * Determine whether the facility can permanently delete the model.
     */
    public function forceDelete(User $user, Facility $model): bool
    {
        return false;
    }
}
