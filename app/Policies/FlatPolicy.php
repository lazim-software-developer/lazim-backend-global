<?php

namespace App\Policies;

use App\Models\Building\Flat;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FlatPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the flat can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the flat can view the model.
     */
    public function view(User $user, Flat $model): bool
    {
        return true;
    }

    /**
     * Determine whether the flat can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the flat can update the model.
     */
    public function update(User $user, Flat $model): bool
    {
        return true;
    }

    /**
     * Determine whether the flat can delete the model.
     */
    public function delete(User $user, Flat $model): bool
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
     * Determine whether the flat can restore the model.
     */
    public function restore(User $user, Flat $model): bool
    {
        return false;
    }

    /**
     * Determine whether the flat can permanently delete the model.
     */
    public function forceDelete(User $user, Flat $model): bool
    {
        return false;
    }
}
