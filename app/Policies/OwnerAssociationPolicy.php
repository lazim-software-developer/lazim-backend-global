<?php

namespace App\Policies;

use App\Models\OwnerAssociation;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OwnerAssociationPolicy
{
    /**
     * Create a new policy instance.
     */

    use HandlesAuthorization;

    /**
     * Determine whether the attendance can view any models.
     */

    public function viewAny(User $user): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the attendance can view the model.
     */
    public function view(User $user, OwnerAssociation $model): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the attendance can create models.
     */
    public function create(User $user): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the attendance can update the model.
     */
    public function update(User $user, OwnerAssociation $model): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the attendance can delete the model.
     */
    public function delete(User $user, OwnerAssociation $model): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

        return false;

    }

    /**
     * Determine whether the user can delete multiple instances of the model.
     */
    public function deleteAny(User $user): bool
    {
        $role = $user->role;

        return $role && $role->name == 'Admin';

    }

    /**
     * Determine whether the attendance can restore the model.
     */
    public function restore(User $user, OwnerAssociation $model): bool
    {
        return false;
    }

    /**
     * Determine whether the attendance can permanently delete the model.
     */
    public function forceDelete(User $user, OwnerAssociation $model): bool
    {
        return false;
    }

    public function __construct()
    {
        //
    }
}
