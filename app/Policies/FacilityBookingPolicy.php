<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Building\FacilityBooking;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacilityBookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the facilityBooking can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the facilityBooking can view the model.
     */
    public function view(User $user, FacilityBooking $model): bool
    {
        return true;
    }

    /**
     * Determine whether the facilityBooking can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the facilityBooking can update the model.
     */
    public function update(User $user, FacilityBooking $model): bool
    {
        return true;
    }

    /**
     * Determine whether the facilityBooking can delete the model.
     */
    public function delete(User $user, FacilityBooking $model): bool
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
     * Determine whether the facilityBooking can restore the model.
     */
    public function restore(User $user, FacilityBooking $model): bool
    {
        return false;
    }

    /**
     * Determine whether the facilityBooking can permanently delete the model.
     */
    public function forceDelete(User $user, FacilityBooking $model): bool
    {
        return false;
    }
}
