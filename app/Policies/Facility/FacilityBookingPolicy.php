<?php

namespace App\Policies\Facility;

use App\Models\FacilityBooking;
use App\Models\User\User;

class FacilityBookingPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $buildingId)
    {
        // If the user is a tenant or Owner, they can book a facility
        if (in_array($user->role->name, ['Owner', 'Tenant'])) {
            return $user->residences()
                ->whereHas('building', function ($query) use ($buildingId) {
                    $query->where('id', $buildingId);
                })
                ->where('active', 1)
                ->exists();
        }

        // TODO: Write logic for allowing other roles if needed

        // If the user's role is not owner or tenant, they can't book the facility
        return false;
    }
}
