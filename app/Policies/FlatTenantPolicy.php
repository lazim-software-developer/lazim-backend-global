<?php

namespace App\Policies;

use App\Models\User\User;
use App\Models\Building\FlatTenant;
use Illuminate\Auth\Access\HandlesAuthorization;

class FlatTenantPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the flatTenant can view any models.
     */
    public function viewAny(User $user): bool
    {
        $role = $user->role->name == 'Admin';

        if($role)
        {
            return false;
        }
        return true;
    }

    /**
     * Determine whether the flatTenant can view the model.
     */
    public function view(User $user, FlatTenant $model): bool
    {
        return true;
    }

    /**
     * Determine whether the flatTenant can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the flatTenant can update the model.
     */
    public function update(User $user, FlatTenant $model): bool
    {
        return true;
    }

    /**
     * Determine whether the flatTenant can delete the model.
     */
    public function delete(User $user, FlatTenant $model): bool
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
     * Determine whether the flatTenant can restore the model.
     */
    public function restore(User $user, FlatTenant $model): bool
    {
        return false;
    }

    /**
     * Determine whether the flatTenant can permanently delete the model.
     */
    public function forceDelete(User $user, FlatTenant $model): bool
    {
        return false;
    }
}
