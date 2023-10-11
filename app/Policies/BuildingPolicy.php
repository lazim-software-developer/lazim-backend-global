<?php

namespace App\Policies;

use App\Models\Building\Building;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuildingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the building can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->id == 1 || $user->id == 2) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the building can view the model.
     */
    public function view(User $user, Building $model): bool
    {
        if ($user->id == 1 || $user->id == 2) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the building can create models.
     */
    public function create(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the building can update the model.
     */
    public function update(User $user, Building $model): bool
    {
        if ($user->id == 1 || $user->id == 2) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the building can delete the model.
     */
    public function delete(User $user, Building $model): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the building can restore the model.
     */
    public function restore(User $user, Building $model): bool
    {
        return false;
    }

    /**
     * Determine whether the building can permanently delete the model.
     */
    public function forceDelete(User $user, Building $model): bool
    {
        return false;
    }
}
