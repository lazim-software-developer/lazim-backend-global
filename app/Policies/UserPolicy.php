<?php

namespace App\Policies;

use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role_id == 9|| $user->role_id == 10) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->role_id == 9|| $user->role_id == 10) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role_id == 9|| $user->role_id == 10) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->role_id == 9|| $user->role_id == 10) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->role_id == 9|| $user->role_id == 10) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        if ($user->role_id == 9|| $user->role_id == 10) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
