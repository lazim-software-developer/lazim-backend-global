<?php

namespace App\Policies;

use App\Models\Building\BuildingPoc;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuildingPocPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the buildingPoc can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the buildingPoc can view the model.
     */
    public function view(User $user, BuildingPoc $model): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the buildingPoc can create models.
     */
    public function create(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the buildingPoc can update the model.
     */
    public function update(User $user, BuildingPoc $model): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the buildingPoc can delete the model.
     */
    public function delete(User $user, BuildingPoc $model): bool
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
     * Determine whether the buildingPoc can restore the model.
     */
    public function restore(User $user, BuildingPoc $model): bool
    {
        return false;
    }

    /**
     * Determine whether the buildingPoc can permanently delete the model.
     */
    public function forceDelete(User $user, BuildingPoc $model): bool
    {
        return false;
    }
}
