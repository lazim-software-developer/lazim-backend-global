<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Vendor\Attendance;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the attendance can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the attendance can view the model.
     */
    public function view(User $user, Attendance $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the attendance can create models.
     */
    public function create(User $user): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the attendance can update the model.
     */
    public function update(User $user, Attendance $model): bool
    {
        if ($user->role_id == 9) {
            return true;
        }

        return false;

    }

    /**
     * Determine whether the attendance can delete the model.
     */
    public function delete(User $user, Attendance $model): bool
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
     * Determine whether the attendance can restore the model.
     */
    public function restore(User $user, Attendance $model): bool
    {
        return false;
    }

    /**
     * Determine whether the attendance can permanently delete the model.
     */
    public function forceDelete(User $user, Attendance $model): bool
    {
        return false;
    }
}
