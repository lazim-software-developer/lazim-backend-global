<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Visitor\FlatDomesticHelp;
use Illuminate\Auth\Access\HandlesAuthorization;

class FlatDomesticHelpPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the flatDomesticHelp can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the flatDomesticHelp can view the model.
     */
    public function view(User $user, FlatDomesticHelp $model): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the flatDomesticHelp can create models.
     */
    public function create(User $user): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the flatDomesticHelp can update the model.
     */
    public function update(User $user, FlatDomesticHelp $model): bool
    {
        if ($user->id == 1) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the flatDomesticHelp can delete the model.
     */
    public function delete(User $user, FlatDomesticHelp $model): bool
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
     * Determine whether the flatDomesticHelp can restore the model.
     */
    public function restore(User $user, FlatDomesticHelp $model): bool
    {
        return false;
    }

    /**
     * Determine whether the flatDomesticHelp can permanently delete the model.
     */
    public function forceDelete(User $user, FlatDomesticHelp $model): bool
    {
        return false;
    }
}
