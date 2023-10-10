<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Vendor\Contact;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the contact can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the contact can view the model.
     */
    public function view(User $user, Contact $model): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the contact can create models.
     */
    public function create(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the contact can update the model.
     */
    public function update(User $user, Contact $model): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the contact can delete the model.
     */
    public function delete(User $user, Contact $model): bool
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
     * Determine whether the contact can restore the model.
     */
    public function restore(User $user, Contact $model): bool
    {
        return false;
    }

    /**
     * Determine whether the contact can permanently delete the model.
     */
    public function forceDelete(User $user, Contact $model): bool
    {
        return false;
    }
}
