<?php

namespace App\Policies;

use App\Models\Building\Complaint;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComplaintPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the complaint can view any models.
     */
    public function viewAny(User $user): bool
    {
        $allowedRoles = ['OA','Admin'];

        if (in_array($user->role->name, $allowedRoles)) {
        return true;
        }
    }

    /**
     * Determine whether the complaint can view the model.
     */
    public function view(User $user, Complaint $model): bool
    {
        $allowedRoles = ['OA','Admin'];

        if (in_array($user->role->name, $allowedRoles)) {
        return true;
        }
    }

    /**
     * Determine whether the complaint can create models.
     */
    public function create(User $user): bool
    {
        $allowedRoles = ['OA','Admin'];

        if (in_array($user->role->name, $allowedRoles)) {
        return true;
        }
    }

    /**
     * Determine whether the complaint can update the model.
     */
    public function update(User $user, Complaint $model): bool
    {
        return false;
    }

    /**
     * Determine whether the complaint can delete the model.
     */
    public function delete(User $user, Complaint $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
      return false;
    }

    /**
     * Determine whether the complaint can restore the model.
     */
    // public function restore(User $user, Complaint $model): bool
    // {
    //     return false;
    // }

    // /**
    //  * Determine whether the complaint can permanently delete the model.
    //  */
    // public function forceDelete(User $user, Complaint $model): bool
    // {
    //     return false;
    // }
}
